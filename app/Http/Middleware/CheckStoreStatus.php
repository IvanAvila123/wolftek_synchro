<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Filament\Notifications\Notification;

class CheckStoreStatus
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->routeIs('filament.admin.pages.billing')) {
        return $next($request);
    }
        /** @var User|null $user */
        $user = Auth::user();

        if (!$user) {
            return $next($request);
        }

        if ($user->hasDirectRole(['super_admin'])) {
            return $next($request);
        }

        $store = $user->store ?? ($user->employee ? $user->employee->store : null);

        if ($store) {
            // Variable para saber si debemos bloquear el acceso
            $shouldBlock = false;
            $blockMessage = '';

            // Condición 1: El Superadmin apagó la tienda manualmente
            if (!$store->is_active) {
                $shouldBlock = true;
                $blockMessage = 'Tu cuenta ha sido desactivada por el administrador.';
            } 
            // Condición 2: Falta de pago recurrente
            elseif (in_array($store->estatus, ['canceled', 'unpaid', 'past_due'])) {
                $shouldBlock = true;
                $blockMessage = 'Tu cuenta está suspendida por falta de pago. Contacta a soporte.';
            }
            // Condición 3: ¡Se acabó la prueba gratis!
            elseif ($store->estatus === 'trial') {
                // Comparamos si la fecha actual ya superó la fecha de fin de prueba
                if ($store->trial_ends_at && now()->greaterThan($store->trial_ends_at)) {
                    $shouldBlock = true;
                    $blockMessage = 'Tu periodo de prueba de 7 días ha terminado. Por favor, elige un plan para continuar.';
                    
                    // Opcional: Podrías actualizar el estatus de la tienda aquí mismo
                    // $store->update(['estatus' => 'past_due', 'is_active' => false]);
                }
            }

            // Si se cumplió alguna condición para bloquear
           if ($shouldBlock) {
                // Si es el dueño, NO lo deslogueamos, lo mandamos a pagar
                if ($user->hasDirectRole(['owner'])) {
                    Notification::make()
                        ->title('Acceso Restringido ⛔')
                        ->body($blockMessage)
                        ->danger()
                        ->send();

                    return redirect()->route('filament.admin.pages.billing', ['tenant' => $store]);
                }

                // Cualquier otro rol (manager, cajero, custom) -> logout
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                Notification::make()
                    ->title('Acceso Suspendido ⛔')
                    ->body('El acceso de la tienda está suspendido. Contacta al dueño.')
                    ->danger()
                    ->send();

                return redirect(filament()->getLoginUrl());
            }
        }

        return $next($request);
    }
}