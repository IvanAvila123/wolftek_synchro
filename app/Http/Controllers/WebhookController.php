<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
}
