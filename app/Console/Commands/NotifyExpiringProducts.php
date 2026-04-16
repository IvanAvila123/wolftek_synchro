<?php

namespace App\Console\Commands;

use App\Models\ProductBatch;
use App\Models\Store;
use App\Models\User;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Console\Command;

class NotifyExpiringProducts extends Command
{
    protected $signature = 'notify:expiring-products {--days=7 : Días antes del vencimiento para avisar}';
    protected $description = 'Notifica a los administradores sobre lotes de productos próximos a caducar';

    public function handle(): void
    {
        $days = (int) $this->option('days');
        $today = now()->toDateString();
        $threshold = now()->addDays($days)->toDateString();

        $batches = ProductBatch::with('product')
            ->where('quantity', '>', 0)
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '>=', $today)
            ->whereDate('expiry_date', '<=', $threshold)
            ->get();

        if ($batches->isEmpty()) {
            $this->info('Sin lotes próximos a caducar.');
            return;
        }

        $byStore = $batches->groupBy('store_id');

        foreach ($byStore as $storeId => $storeBatches) {
            $store = Store::with('owner')->find($storeId);

            if (! $store) {
                continue;
            }

            $recipients = $this->getAdminRecipients($store);

            if ($recipients->isEmpty()) {
                continue;
            }

            $count = $storeBatches->count();
            $names = $storeBatches->take(3)
                ->map(fn ($b) => $b->product?->name)
                ->filter()
                ->implode(', ');
            $remaining = $count - 3;
            $extra = $count > 3 ? " y {$remaining} más..." : '';
            $label = $count === 1 ? 'lote' : 'lotes';

            Notification::make()
                ->title("Próximos a caducar: {$count} {$label}")
                ->body("{$names}{$extra} — vencen en los próximos {$days} días")
                ->icon('heroicon-o-clock')
                ->iconColor('warning')
                ->actions([
                    Action::make('ver')
                        ->label('Ver lotes')
                        ->url("/admin/{$store->id}/product-batches")
                        ->markAsRead(),
                ])
                ->sendToDatabase($recipients);
        }

        $this->info('Notificaciones de caducidad enviadas.');
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
