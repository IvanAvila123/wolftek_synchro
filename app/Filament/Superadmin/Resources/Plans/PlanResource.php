<?php

namespace App\Filament\Superadmin\Resources\Plans;

use App\Filament\Superadmin\Resources\Plans\Pages\ManagePlans;
use App\Models\Plan;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class PlanResource extends Resource
{
    protected static ?string $model = Plan::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;
    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'Plan';
    protected static ?string $pluralModelLabel = 'Planes';
    protected static ?string $navigationLabel = 'Planes';
    protected static string | UnitEnum | null $navigationGroup = 'SaaS';
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nombre del plan')
                    ->required()
                    ->placeholder('Ej: Básico, Profesional, Empresarial'),
                TextInput::make('price')
                    ->label('Precio mensual')
                    ->required()
                    ->numeric()
                    ->prefix('$')
                    ->suffix('MXN'),
                TextInput::make('max_users')
                    ->label('Máximo de usuarios')
                    ->required()
                    ->numeric()
                    ->minValue(1),
                TextInput::make('max_branches')
                    ->label('Máximo de sucursales')
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->helperText('Usa 999 para sucursales ilimitadas'),
                CheckboxList::make('features')
                    ->label('Características incluidas')
                    ->options([
                        'pos'              => 'Punto de Venta',
                        'inventory'        => 'Inventario básico',
                        'suppliers'        => 'Proveedores',
                        'customers'        => 'Clientes y fiado',
                        'scale'            => 'Báscula de precio',
                        'labels'           => 'Etiquetador',
                        'batches'          => 'Lotes y caducidades',
                        'online_catalog'         => 'Catalogo en linea',
                        'reports'          => 'Reportes avanzados',
                        'multi_branch'     => 'Multi-sucursal',
                        'expenses'         => 'Control de gastos',
                        'loyalty'          => 'Programa de lealtad',
                        'api'              => 'Acceso API',
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Plan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('price')
                    ->label('Precio')
                    ->money('MXN')
                    ->sortable(),
                TextColumn::make('max_users')
                    ->label('Usuarios')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('max_branches')
                    ->label('Sucursales')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('stores_count')
                    ->label('Tiendas activas')
                    ->counts('stores')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManagePlans::route('/'),
        ];
    }
}
