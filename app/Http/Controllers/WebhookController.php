<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Store;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\Client\PreApproval\PreApprovalClient;
use MercadoPago\MercadoPagoConfig;

class WebhookController extends Controller
{
    public function handleConekta(Request $request)
    {
        // ── 1. Verificar firma de Conekta ──────────────────────────────────
        if (! $this->isValidSignature($request)) {
            Log::warning('Webhook rechazado: firma inválida.');
            return response()->json(['status' => 'unauthorized'], 401);
        }

        try {
            $payload = $request->all();

            Log::info('--- LLEGÓ UN WEBHOOK DE CONEKTA ---');
            Log::info('Tipo: ' . ($payload['type'] ?? 'Ninguno'));

            if (isset($payload['type']) && $payload['type'] === 'order.paid') {
                $order    = $payload['data']['object'];
                $metadata = $order['metadata'] ?? null;

                if ($metadata && isset($metadata['store_id'])) {
                    $store = Store::find($metadata['store_id']);

                    if ($store) {
                        $store->update([
                            'plan_id'     => $metadata['plan_id'],
                            'is_active'   => true,
                            'estatus'     => 'active',
                            'valid_until' => now()->addMonth(),
                        ]);

                        $paymentMethod = $order['charges']['data'][0]['payment_method']['object'] ?? 'card';

                        Subscription::create([
                            'store_id'                => $store->id,
                            'plan_id'                 => $metadata['plan_id'],
                            'estatus'                 => 'active',
                            'starts_at'               => now(),
                            'ends_at'                 => now()->addMonth(),
                            'payment_method'          => $paymentMethod,
                            'conekta_subscription_id' => $order['id'],
                        ]);

                        Log::info('¡Éxito! Tienda ' . $store->id . ' activada.');
                    } else {
                        Log::error('Webhook: No se encontró la tienda ID ' . $metadata['store_id']);
                    }
                } else {
                    Log::error('Webhook: La orden no traía metadata con store_id.');
                }
            }

            return response()->json(['status' => 'success'], 200);

        } catch (\Exception $e) {
            Log::error('Fallo en el Webhook: ' . $e->getMessage());
            return response()->json(['status' => 'error'], 500);
        }
    }

    // ── Verificación de firma RSA de Conekta ──────────────────────────────
    private function isValidSignature(Request $request): bool
    {
        $rawKey = config('conekta.webhook_public_key');

        // Si no hay llave configurada, dejamos pasar (para no romper en dev)
        if (empty($rawKey)) {
            Log::warning('Webhook: CONEKTA_WEBHOOK_PUBLIC_KEY no configurada. Saltando verificación.');
            return true;
        }

        $digestHeader = $request->header('Digest');
        if (! $digestHeader) {
            return false;
        }

        // Reconstruir PEM a partir del base64 guardado en .env
        $pem = "-----BEGIN PUBLIC KEY-----\n"
             . chunk_split($rawKey, 64, "\n")
             . "-----END PUBLIC KEY-----\n";

        $publicKey = openssl_pkey_get_public($pem);
        if (! $publicKey) {
            Log::error('Webhook: No se pudo parsear la llave pública RSA.');
            return false;
        }

        $signature = base64_decode($digestHeader);
        $body      = $request->getContent();

        $result = openssl_verify($body, $signature, $publicKey, OPENSSL_ALGO_SHA256);

        return $result === 1;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MERCADO PAGO
    // ─────────────────────────────────────────────────────────────────────────

    public function handleMercadoPago(Request $request)
    {
        Log::info('MP Webhook recibido', $request->all());

        $topic = $request->query('topic') ?? $request->input('type');
        $id    = $request->query('id')    ?? $request->input('data.id');

        // MP envía distintos tipos: "payment" o "subscription_authorized_payment"
        if (! in_array($topic, ['payment', 'subscription_authorized_payment'])) {
            return response()->json(['status' => 'ignored'], 200);
        }

        if (empty($id)) {
            return response()->json(['status' => 'no_id'], 200);
        }

        try {
            MercadoPagoConfig::setAccessToken(config('mercadopago.access_token'));

            // 1. Obtener detalles del pago
            $paymentClient = new PaymentClient();
            $payment       = $paymentClient->get((int) $id);

            Log::info('MP Payment status: ' . $payment->status);

            if ($payment->status !== 'approved') {
                return response()->json(['status' => 'not_approved'], 200);
            }

            // 2. Obtener external_reference desde la suscripción (preapproval)
            $externalRef = $payment->external_reference;

            if (empty($externalRef) && ! empty($payment->preapproval_id)) {
                $preapprovalClient = new PreApprovalClient();
                $preapproval       = $preapprovalClient->get($payment->preapproval_id);
                $externalRef       = $preapproval->external_reference ?? null;
            }

            Log::info('MP external_reference: ' . $externalRef);

            // Formato esperado: store_{id}_plan_{id}
            if (! $externalRef || ! str_starts_with($externalRef, 'store_')) {
                Log::error('MP Webhook: external_reference inválido: ' . $externalRef);
                return response()->json(['status' => 'no_reference'], 200);
            }

            // 3. Parsear store_id y plan_id
            preg_match('/store_(\d+)_plan_(\d+)/', $externalRef, $matches);
            $storeId = $matches[1] ?? null;
            $planId  = $matches[2] ?? null;

            if (! $storeId || ! $planId) {
                Log::error('MP Webhook: no se pudo parsear store_id/plan_id de: ' . $externalRef);
                return response()->json(['status' => 'parse_error'], 200);
            }

            $store = Store::find($storeId);
            $plan  = Plan::find($planId);

            if (! $store || ! $plan) {
                Log::error("MP Webhook: store {$storeId} o plan {$planId} no encontrado.");
                return response()->json(['status' => 'not_found'], 200);
            }

            // 4. Extender valid_until 30 días
            $newValidUntil = now()->gt($store->valid_until ?? now())
                ? now()->addDays(30)
                : \Carbon\Carbon::parse($store->valid_until)->addDays(30);

            $store->update([
                'plan_id'     => $plan->id,
                'is_active'   => true,
                'estatus'     => 'activo',
                'valid_until' => $newValidUntil,
            ]);

            // 5. Registrar en historial de suscripciones
            Subscription::create([
                'store_id'          => $store->id,
                'plan_id'           => $plan->id,
                'estatus'           => 'activo',
                'payment_method'    => 'tarjeta',
                'mp_subscription_id'=> $payment->preapproval_id ?? null,
                'mp_payment_id'     => (string) $payment->id,
                'starts_at'         => now(),
                'ends_at'           => $newValidUntil,
            ]);

            Log::info("MP Webhook: Tienda {$store->id} ({$store->name}) activada hasta {$newValidUntil}.");

            return response()->json(['status' => 'success'], 200);

        } catch (\Exception $e) {
            Log::error('MP Webhook error: ' . $e->getMessage());
            return response()->json(['status' => 'error'], 500);
        }
    }
}
