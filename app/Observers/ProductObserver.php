<?php

namespace App\Observers;

use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;

class ProductObserver
{
    public function updated(Product $product): void
    {
        // Solo dispara cuando el stock cambia y cae al mínimo
        if (! $product->wasChanged('stock')) {
            return;
        }

        if (is_null($product->stock_min)) {
            return;
        }

        $stockBefore = $product->getOriginal('stock');

        // Solo notifica cuando cruza el umbral (de arriba hacia abajo), no en cada venta
        if ($stockBefore <= $product->stock_min) {
            return;
        }

        if ($product->stock > $product->stock_min) {
            return;
        }

        $store = Store::with('owner')->find($product->store_id);
        if (! $store) {
            return;
        }

        $recipients = $this->getAdminRecipients($store);
        if ($recipients->isEmpty()) {
            return;
        }

        Notification::make()
            ->title('Stock bajo: ' . $product->name)
            ->body("Quedan **{$product->stock}** {$product->unidad} (mínimo: {$product->stock_min})")
            ->icon('heroicon-o-exclamation-triangle')
            ->iconColor('danger')
            ->actions([
                Action::make('ver')
                    ->label('Ver producto')
                    ->url("/admin/{$product->store_id}/products/{$product->id}")
                    ->markAsRead(),
            ])
            ->sendToDatabase($recipients);
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
