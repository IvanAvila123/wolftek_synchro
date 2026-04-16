<?php

namespace App\Filament\Cashier\Widgets;

use App\Models\CashShift;
use App\Models\CreditPayment;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Dashboard\Actions\FilterAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class LatestCreditPayments extends TableWidget
{
    // Título de la tabla
    protected static ?string $heading = 'Últimos abonos de este turno';

    // Hacemos que la tabla ocupe todo el ancho de la página
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        // Buscamos el turno abierto del cajero actual
        $shift = CashShift::where('user_id', auth()->id())->where('status', 'open')->first();

        return $table
            ->query(
                // Traemos los abonos solo de este turno
                CreditPayment::query()
                    ->when($shift, fn($query) => $query->where('cash_shift_id', $shift->id))
                    ->when(!$shift, fn($query) => $query->whereRaw('1 = 0')) // Si no hay turno, tabla vacía
                    ->latest()
            )
            ->columns([
                TextColumn::make('customer.name')
                    ->label('Cliente')
                    ->searchable()
                    ->weight('bold'),
                    
                TextColumn::make('amount')
                    ->label('Monto')
                    ->money('MXN')
                    ->color('success')
                    ->weight('bold'),
                    
                TextColumn::make('payment_method')
                    ->label('Método')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'cash' => 'Efectivo',
                        'card' => 'Tarjeta',
                        'transfer' => 'Transferencia',
                        default => 'Otro',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'cash' => 'success',
                        'card' => 'info',
                        'transfer' => 'warning',
                        default => 'gray',
                    }),
                    
                TextColumn::make('created_at')
                    ->label('Hora')
                    ->time('H:i'),
            ])
            ->actions([
                // El mismo botón de imprimir ticket
                Action::make('imprimir')
                    ->label('Reimprimir')
                    ->icon('heroicon-o-printer')
                    ->color('gray')
                    ->url(fn (CreditPayment $record): string => route('abono.imprimir', $record->id))
                    ->openUrlInNewTab(),
            ])
            ->paginated([5, 10]); // Mostramos de 5 en 5 para no hacer la pantalla enorme
    }

    protected function getHeaderActions(): array
    {
        return [
            FilterAction::make()
                ->schema([
                    DatePicker::make('startDate'),
                    DatePicker::make('endDate'),
                    // ...
                ]),
        ];
    }
}
