<?php

namespace App\Observers;

use App\Models\OnlineOrder;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class OnlineOrderObserver
{
    public function created(OnlineOrder $order): void
    {
        // Cajeros y managers de la tienda — query directa para evitar team context de Spatie
        $cashierUsers = User::whereHas('employee', fn ($q) => $q->where('store_id', $order->store_id))
            ->whereExists(fn ($q) => $q
                ->from('model_has_roles')
                ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
                ->whereColumn('model_has_roles.model_id', 'users.id')
                ->where('model_has_roles.model_type', User::class)
                ->whereIn('roles.name', ['cashier', 'manager'])
            )->get();

        if ($cashierUsers->isEmpty()) {
            return;
        }

        Notification::make()
            ->title('Nuevo pedido en línea')
            ->body("**{$order->customer_name}** — \${$order->total} MXN — Tel: {$order->customer_phone}")
            ->icon('heroicon-o-shopping-bag')
            ->iconColor('warning')
            ->actions([
                Action::make('ver')
                    ->label('Ver pedidos')
                    ->url("/cashier/{$order->store_id}/online-orders")
                    ->markAsRead(),
            ])
            ->sendToDatabase($cashierUsers);
    }
}
