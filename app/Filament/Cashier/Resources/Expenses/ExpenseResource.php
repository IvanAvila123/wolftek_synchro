<?php

namespace App\Filament\Cashier\Resources\Expenses;

use App\Filament\Cashier\Resources\Expenses\Pages\CreateExpense;
use App\Filament\Cashier\Resources\Expenses\Pages\EditExpense;
use App\Filament\Cashier\Resources\Expenses\Pages\ListExpenses;
use App\Filament\Cashier\Resources\Expenses\Schemas\ExpenseForm;
use App\Filament\Cashier\Resources\Expenses\Tables\ExpensesTable;
use App\Models\Expense;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ExpenseResource extends Resource
{
    protected static ?string $model = Expense::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Ticket;

    protected static ?string $navigationLabel = 'Gastos';

    protected static string |\UnitEnum|null $navigationGroup = 'Ventas';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return ExpenseForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ExpensesTable::configure($table);
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
            'index' => ListExpenses::route('/'),

        ];
    }
}
