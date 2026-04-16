<?php

namespace App\Filament\Pages;

use App\Models\Store;
use App\Models\User;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class StoreSettings extends Page implements HasForms
{
    use InteractsWithForms;

    public static function canAccess(): bool
    {
        /** @var User|null $user */
        $user = Auth::user();
        if ($user?->hasDirectRole(['owner', 'manager'])) {
            return true;
        }
        return $user?->can('page_StoreSettings') ?? false;
    }

protected static ?string $navegationIcon = 'heroicon-o-cog-6-tooth';
    protected static string|\UnitEnum|null $navigationGroup = 'Configuración';
    protected static ?string $navigationLabel = 'Configurar Tienda';
    protected static ?string $title = 'Configuración de la Tienda';
    protected string $view = 'filament.pages.store-settings';

    public ?array $data = [];

    public function mount(): void{
        // Cargamos los datos de la tienda actual en el formulario
        $this->form->fill(
            filament()->getTenant()->toArray()
        );
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('business_type')
                    ->label('Tipo de negocio')
                    ->options(Store::businessTypes())
                    ->required(),
                Select::make('ticket_width')
                    ->label('Ancho de papel para tickets')
                    ->options([
                        '58mm' => '58 mm (impresoras pequeñas)',
                        '72mm' => '72 mm (formato medio)',
                        '80mm' => '80 mm (estándar — más común)',
                    ])
                    ->default('80mm')
                    ->required()
                    ->helperText('Elige el ancho del rollo de tu impresora térmica.'),
                TextInput::make('name')
                    ->label('Nombre del negocio')
                    ->required()
                    ->maxLength(255)
                    ->autofocus(),
                TextInput::make('phone')
                    ->label('Teléfono')
                    ->tel()
                    ->placeholder('55 1234 5678')
                    ->nullable(),
                TextInput::make('address')
                    ->label('Dirección')
                    ->placeholder('Calle, Colonia, CP, Ciudad')
                    ->nullable(),
                TextInput::make('rfc')
                    ->label('RFC')
                    ->placeholder('XAXX010101000')
                    ->afterStateUpdatedJs(<<<'JS'
                        $set('rfc', ($state ?? '').toUpperCase())
                    JS)
                    ->maxLength(13)
                    ->nullable(),
                TextInput::make('whatsapp_number')
                    ->label('Número de WhatsApp')
                    ->tel()
                    ->placeholder('55 1234 5678')
                    ->nullable(),
                Textarea::make('catalog_description')
                    ->label('Descripción para el catálogo en línea')
                    ->placeholder('Ej: Somos un negocio con más de 20 años de experiencia...')
                    ->rows(3)
                    ->nullable(),
                FileUpload::make('logo')
                    ->label('Logo de la tienda')
                    ->image()
                    ->disk('public')
                    ->directory('logos')
                    ->nullable(),
            ])->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        
        // Actualizamos la tienda actual (Tenant)
        filament()->getTenant()->update($data);

        Notification::make()
            ->success()
            ->title('¡Configuración guardada!')
            ->send();
    }
}
