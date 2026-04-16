<?php

namespace App\Filament\Superadmin\Resources\Subscriptions;

use App\Filament\Superadmin\Resources\Subscriptions\Pages\CreateSubscription;
use App\Filament\Superadmin\Resources\Subscriptions\Pages\EditSubscription;
use App\Filament\Superadmin\Resources\Subscriptions\Pages\ListSubscriptions;
use App\Models\Subscription;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SubscriptionResource extends Resource
{
    protected static ?string $model = Subscription::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    protected static ?string $modelLabel = 'Suscripción';

    protected static ?string $pluralModelLabel = 'Suscripciones';

    protected static string|\UnitEnum|null $navigationGroup = 'SaaS';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema->components(self::formComponents());
    }

    public static function formComponents(): array
    {
        return [
            \Filament\Schemas\Components\Section::make('Suscripción')
                ->icon('heroicon-o-credit-card')
                ->columns(2)
                ->schema([
                    \Filament\Forms\Components\Select::make('store_id')
                        ->label('Tienda')
                        ->relationship('store', 'name')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->columnSpanFull(),

                    \Filament\Forms\Components\Select::make('plan_id')
                        ->label('Plan')
                        ->relationship('plan', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),

                    \Filament\Forms\Components\Select::make('estatus')
                        ->label('Estatus')
                        ->options([
                            'activo'     => 'Activo',
                            'suspendido' => 'Suspendido',
                            'cancelado'  => 'Cancelado',
                        ])
                        ->default('activo')
                        ->required()
                        ->native(false),

                    \Filament\Forms\Components\Select::make('payment_method')
                        ->label('Método de Pago')
                        ->options([
                            'tarjeta'  => 'Tarjeta',
                            'spei'     => 'SPEI',
                            'oxxo'     => 'OXXO',
                            'efectivo' => 'Efectivo',
                        ])
                        ->required()
                        ->native(false),

                    \Filament\Forms\Components\TextInput::make('conekta_subscription_id')
                        ->label('ID Conekta')
                        ->placeholder('sub_xxxxxxxx')
                        ->nullable()
                        ->columnSpanFull(),

                    \Filament\Forms\Components\DatePicker::make('starts_at')
                        ->label('Inicio')
                        ->native(false)
                        ->displayFormat('d/m/Y')
                        ->default(now()),

                    \Filament\Forms\Components\DatePicker::make('ends_at')
                        ->label('Vencimiento')
                        ->native(false)
                        ->displayFormat('d/m/Y'),
                ]),
        ];
    }

    public static function table(Table $table): Table
    {
        return \App\Filament\Superadmin\Resources\Subscriptions\Tables\SubscriptionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListSubscriptions::route('/'),
            'create' => CreateSubscription::route('/create'),
            'edit'   => EditSubscription::route('/{record}/edit'),
        ];
    }
}
