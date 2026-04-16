<?php

namespace App\Console\Commands;

use App\Models\Store;
use App\Models\User;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Console\Command;

class NotifyLowStock extends Command
{
    protected $signature = 'notify:low-stock';
    protected $description = 'Notifica a los administradores cuando hay productos con stock bajo';

    public function handle(): void
    {
        $stores = Store::with('owner')->get();

        foreach ($stores as $store) {
            $lowStock = $store->products()
                ->where('is_active', true)
                ->whereColumn('stock', '<=', 'stock_min')
                ->whereNotNull('stock_min')
                ->get();

            if ($lowStock->isEmpty()) {
                continue;
            }

            $recipients = $this->getAdminRecipients($store);

            if ($recipients->isEmpty()) {
                continue;
            }

            $count = $lowStock->count();
            $names = $lowStock->take(3)->pluck('name')->implode(', ');
            $extra = $count > 3 ? " y {$count - 3} más..." : '';

            Notification::make()
                ->title("Stock bajo: {$count} " . ($count === 1 ? 'producto' : 'productos'))
                ->body("{$names}{$extra}")
                ->icon('heroicon-o-exclamation-triangle')
                ->iconColor('danger')
                ->actions([
                    Action::make('ver')
                        ->label('Ver productos')
                        ->url("/admin/{$store->id}/products")
                        ->markAsRead(),
                ])
                ->sendToDatabase($recipients);
        }

        $this->info('Notificaciones de stock bajo enviadas.');
    }

    private function getAdminRecipients(Store $store): \Illuminate\Support\Collection
    {
        $recipients = collect();

        if ($store->owner) {
            $recipients->push($store->owner);
        }

        $adminEmployees = User::whereHas('employee', fn ($q) => $q->where('store_id', $store->id))
            ->whereExists(fn ($q) => $q
                ->from('model_has_roles')
                ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
                ->whereColumn('model_has_roles.model_id', 'users.id')
                ->where('model_has_roles.model_type', User::class)
                ->where('roles.panel', 'admin')
            )->get();

        foreach ($adminEmployees as $user) {
            if (! $recipients->contains('id', $user->id)) {
                $recipients->push($user);
            }
        }

        return $recipients;
    }
}
