<?php

namespace App\Filament\Cashier\Resources\OnlineOrders\Tables;

use App\Models\CashShift;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class OnlineOrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->poll('10s')
            ->columns([
                TextColumn::make('id')
                    ->label('Folio')
                    ->sortable()
                    ->searchable()
                    ->prefix('#00'),
                TextColumn::make('customer_name')
                    ->label('Cliente')
                    ->searchable()
                    ->weight('bold'),
                TextColumn::make('total')
                    ->label('Total a Cobrar')
                    ->money('MXN')
                    ->color('success')
                    ->weight('bold'),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending'   => 'warning',
                        'ready'     => 'info',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'pending'   => '⏱️ Pendiente (Armar)',
                        'ready'     => '🛍️ Listo (Esperando cliente)',
                        'completed' => '✅ Entregado y Cobrado',
                        'cancelled' => '❌ Cancelado',
                    }),
                TextColumn::make('created_at')
                    ->label('Hora del Pedido')
                    ->since() // Muestra "hace 5 minutos"
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])->defaultSort('created_at', 'desc')
            ->filters([
                // Mostrar solo los pendientes por defecto
                SelectFilter::make('status')
                    ->label('Filtrar por Estado')
                    ->options([
                        'pending' => 'Pendientes',
                        'ready' => 'Listos para entregar',
                        'completed' => 'Completados',
                    ])
                    ->default('pending'),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Ver Pedido'),
                Action::make('avisar')
                    ->label('Avisar')
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->color('info')
                    ->visible(fn($record) => $record->status === 'pending')
                    ->action(function ($record) {
                        $record->update(['status' => 'ready']);
                    })
                    ->url(function ($record) {
                        $items = is_string($record->cart_items)
                            ? json_decode($record->cart_items, true)
                            : $record->cart_items;

                        $storeName = $record->store->name ?? 'la tienda';

                        // Armamos la lista de productos
                        $lista = '';
                        if (!empty($items)) {
                            foreach ($items as $item) {
                                $subtotal = number_format(($item['price'] ?? 0) * ($item['qty'] ?? 0), 2);
                                $lista .= "  • {$item['qty']}x {$item['name']} — \${$subtotal}\n";
                            }
                        }

                        $total = number_format($record->total, 2);

                        $mensaje = "🏪 *{$storeName}*\n"
                            . "━━━━━━━━━━━━━━━\n\n"
                            . "¡Hola *{$record->customer_name}*! 👋\n\n"
                            . "Tu pedido *#00{$record->id}* ya está *listo y separado* en mostrador.\n\n"
                            . "📦 *Tu pedido:*\n"
                            . $lista . "\n"
                            . "💰 *Total a pagar: \${$total} MXN*\n\n"
                            . ($record->notes ? "📝 Tus notas: _{$record->notes}_\n\n" : "")
                            . "¡Te esperamos! 🙌\n"
                            . "Recuerda traer tu pago en *efectivo, tarjeta o transferencia*.";

                        return "https://wa.me/52{$record->customer_phone}?text=" . urlencode($mensaje);
                    })
                    ->openUrlInNewTab(),
                // 🖨️ BOTÓN IMPRIMIR: Abre el ticket para impresora térmica
                Action::make('imprimir')
                    ->label('Ticket')
                    ->icon('heroicon-o-printer')
                    ->color('gray')
                    ->url(fn($record) => route('pedido-online', $record->id))
                    ->openUrlInNewTab(),
                // 💵 BOTÓN COBRAR: Transforma el pedido en una Venta real
                Action::make('cobrar')
                    ->label('Cobrar y Entregar')
                    ->icon('heroicon-o-currency-dollar')
                    ->color('success')
                    ->visible(fn($record) => in_array($record->status, ['pending', 'ready']))
                    ->requiresConfirmation()
                    ->modalHeading('Completar Venta del Pedido')
                    ->modalDescription('Al confirmar, se descontarán los productos de tu inventario y el dinero se sumará a tu corte de caja.')
                    ->form([
                        Select::make('payment_method')
                            ->label('Método de Pago')
                            ->options([
                                'cash' => 'Efectivo',
                                'card' => 'Tarjeta',
                                'transfer' => 'Transferencia',
                            ])
                            ->default('cash')
                            ->required(),
                    ])
                    ->action(function (array $data, $record) {
                        $user = auth()->user();
                        // 1. Validar caja abierta
                        $shift = CashShift::where('user_id', $user->id)->where('status', 'open')->first();

                        if (!$shift) {
                            Notification::make()->title('Error')->body('Debes abrir tu turno de caja primero.')->danger()->send();
                            return;
                        }

                        DB::transaction(function () use ($data, $record, $user, $shift) {
                            // 2. Crear la Venta en el POS
                            $sale = Sale::create([
                                'store_id'       => $record->store_id,
                                'cash_shift_id'  => $shift->id,
                                'user_id'        => $user->id,
                                'total'          => $record->total,
                                'payment_method' => $data['payment_method'],
                                'status'         => 'completed',
                            ]);

                            // 3. Procesar los productos (JSON a BD) y descontar stock
                            $items = json_decode($record->cart_items, true);
                            foreach ($items as $item) {
                                SaleItem::create([
                                    'sale_id'    => $sale->id,
                                    'product_id' => $item['id'],
                                    'quantity'   => $item['qty'],
                                    'price'      => $item['price'],
                                    'subtotal'   => $item['price'] * $item['qty'],
                                ]);

                                // Restar del inventario
                                Product::where('id', $item['id'])->decrement('stock', $item['qty']);
                            }

                            // 4. Marcar el pedido online como completado
                            $record->update(['status' => 'completed']);
                        });

                        Notification::make()->title('¡Venta completada y stock actualizado!')->success()->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
