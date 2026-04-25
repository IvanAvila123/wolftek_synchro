<?php

namespace App\Observers;

use App\Models\OnlineOrder;
use App\Models\User;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;

class OnlineOrderObserver
{
    public function created(OnlineOrder $order): void
    {
        // Dueño de la tienda
        $ownerUsers = User::where('id', function ($q) use ($order) {
            $q->select('user_id')->from('stores')->where('id', $order->store_id);
        })->get();

        // Cajeros y managers empleados de la tienda
        $cashierUsers = User::whereHas('employee', fn ($q) => $q->where('store_id', $order->store_id))
            ->whereExists(fn ($q) => $q
                ->from('model_has_roles')
                ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
                ->whereColumn('model_has_roles.model_id', 'users.id')
                ->where('model_has_roles.model_type', User::class)
                ->whereIn('roles.name', ['cashier', 'manager'])
            )->get();

        $recipients = $ownerUsers->merge($cashierUsers)->unique('id');

        if ($recipients->isEmpty()) {
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
            ->sendToDatabase($recipients);
    }
}
