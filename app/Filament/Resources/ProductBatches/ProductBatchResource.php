<?php

namespace App\Filament\Resources\ProductBatches;

use App\Filament\Resources\ProductBatches\Pages\ListProductBatches;
use App\Filament\Resources\ProductBatches\Pages\ViewProductBatch;
use App\Filament\Resources\ProductBatches\Schemas\ProductBatchForm;
use App\Filament\Resources\ProductBatches\Schemas\ProductBatchInfolist;
use App\Filament\Resources\ProductBatches\Tables\ProductBatchesTable;
use App\Models\ProductBatch;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ProductBatchResource extends Resource
{
    protected static ?string $model = ProductBatch::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedListBullet;

    protected static ?string $modelLabel = 'Lote de Producto';

    protected static ?string $pluralModelLabel = 'Lotes de Productos';

    protected static string|\UnitEnum|null $navigationGroup = 'Inventario';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'name';

    public static function canAccess(): bool
    {
        return true;
    }

    public static function getNavigationBadge(): ?string
    {
        return Filament::getTenant()?->hasFeature('batches') ? null : '🔒';
    }

    public static function form(Schema $schema): Schema
    {
        return ProductBatchForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ProductBatchInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProductBatchesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProductBatches::route('/'),
            'view' => ViewProductBatch::route('/{record}'),
        ];
    }
}
