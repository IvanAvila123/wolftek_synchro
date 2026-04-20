<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\CashShift;

class EnsureOpenCashShift
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (!$user) {
            return $next($request);
        }

        // 1. Verificamos si tiene turno abierto
        $hasOpenShift = CashShift::where('user_id', $user->id)
            ->where('status', 'open')
            ->exists();

        $currentRoute = $request->route()->getName();
        $openShiftRoute = 'filament.cashier.pages.apertura-caja';

        // 2. Extraemos el tenant (tienda) de la URL actual
        $tenant = $request->route('tenant');

        // 3. Pasamos el ['tenant' => $tenant] a las redirecciones
        if (!$hasOpenShift && $currentRoute !== $openShiftRoute) {
            return redirect()->route($openShiftRoute, ['tenant' => $tenant]);
        }

        if ($hasOpenShift && $currentRoute === $openShiftRoute) {
            return redirect()->route('filament.cashier.pages.pos', ['tenant' => $tenant]);
        }

        return $next($request);
    }
}