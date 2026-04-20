<?php

namespace App\Filament\Superadmin\Resources\Stores\Schemas;

use App\Models\Store;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class StoreForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('store-tabs')
                    ->columnSpanFull()
                    ->persistTabInQueryString()
                    ->tabs([

                        // ═══════════════════════════════════════════════
                        //  TAB 1 — Información General
                        // ═══════════════════════════════════════════════
                        Tab::make('Tienda')
                            ->icon('heroicon-o-building-storefront')
                            ->schema([
                                Group::make()
                                    ->schema([
                                        Section::make('Información General')
                                            ->description('Datos básicos de la tienda')
                                            ->icon('heroicon-o-identification')
                                            ->iconColor('primary')
                                            ->columns(2)
                                            ->schema([
                                                TextInput::make('name')
                                                    ->label('Nombre del Negocio')
                                                    ->required()
                                                    ->maxLength(255)
                                                    ->placeholder('Ej. Mi Negocio')
                                                    ->autocomplete(false)
                                                    ->columnSpanFull(),
                                                Select::make('business_type')
                                                    ->label('Tipo de Negocio')
                                                    ->options(Store::businessTypes())
                                                    ->required()
                                                    ->native(false)
                                                    ->columnSpanFull(),
                                                Select::make('user_id')
                                                    ->label('Dueño')
                                                    ->relationship('owner', 'name')
                                                    ->searchable()
                                                    ->preload()
                                                    ->required()
                                                    ->helperText('Usuario administrador de esta tienda'),
                                                TextInput::make('phone')
                                                    ->label('Teléfono')
                                                    ->tel()
                                                    ->maxLength(20)
                                                    ->placeholder('55 1234 5678')
                                                    ->mask('99 9999 9999'),
                                                TextInput::make('rfc')
                                                    ->label('RFC')
                                                    ->maxLength(13)
                                                    ->placeholder('XAXX010101000')
                                                    ->helperText('Opcional — para facturación'),
                                                TextInput::make('address')
                                                    ->label('Dirección')
                                                    ->maxLength(500)
                                                    ->placeholder('Calle, Colonia, Municipio, CP')
                                                    ->columnSpanFull(),
                                            ]),
                                    ])->columnSpan(2),

                                Group::make()
                                    ->schema([
                                        Section::make('Suscripción')
                                            ->icon('heroicon-o-credit-card')
                                            ->iconColor('warning')
                                            ->schema([
                                                Select::make('plan_id')
                                                    ->label('Plan')
                                                    ->relationship('plan', 'name')
                                                    ->searchable()
                                                    ->preload()
                                                    ->placeholder('Seleccionar plan...'),
                                                Select::make('estatus')
                                                    ->label('Estatus')
                                                    ->options([
                                                        'trial'    => '🧪 En Prueba',
                                                        'active'   => '✅ Activa',
                                                        'past_due' => '⚠️ Pago Vencido',
                                                        'canceled' => '🚫 Cancelada',
                                                        'unpaid'   => '⛔ Suspendida',
                                                    ])
                                                    ->default('trial')
                                                    ->native(false),
                                                DatePicker::make('trial_ends_at')
                                                    ->label('Fin de Prueba')
                                                    ->native(false)
                                                    ->displayFormat('d/M/Y')
                                                    ->placeholder('Sin prueba'),
                                                DatePicker::make('valid_until')
                                                    ->label('Próximo Pago')
                                                    ->native(false)
                                                    ->displayFormat('d/M/Y')
                                                    ->placeholder('Sin fecha'),
                                                Toggle::make('is_active')
                                                    ->label('Tienda Activa')
                                                    ->helperText('Desactiva para bloquear acceso')
                                                    ->default(true)
                                                    ->onColor('success')
                                                    ->offColor('danger'),
                                            ]),
                                    ])->columnSpan(1),
                            ])->columns(3),

                        // ═══════════════════════════════════════════════
                        //  TAB 2 — Catálogo en Línea
                        // ═══════════════════════════════════════════════
                        Tab::make('Catálogo')
                            ->icon('heroicon-o-globe-alt')
                            ->badge(fn ($record) => $record?->logo_path ? '✓' : null)
                            ->badgeColor('success')
                            ->schema([
                                Group::make()
                                    ->schema([
                                        Section::make('Logo y Marca')
                                            ->description('La imagen que verán tus clientes')
                                            ->icon('heroicon-o-photo')
                                            ->iconColor('primary')
                                            ->schema([
                                                FileUpload::make('logo_path')
                                                    ->label('Logo de la Tienda')
                                                    ->image()
                                                    ->disk('public')
                                                    ->avatar()
                                                    ->directory('logos')
                                                    ->maxSize(2048)
                                                    ->imageResizeMode('cover')
                                                    ->imageCropAspectRatio('1:1')
                                                    ->imageResizeTargetWidth('256')
                                                    ->imageResizeTargetHeight('256')
                                                    ->helperText('Cuadrado, máx 2MB. Se mostrará en círculo.'),
                                                Textarea::make('catalog_description')
                                                    ->label('Descripción del Catálogo')
                                                    ->placeholder('Ej. Tu tiendita de confianza con los mejores precios del barrio')
                                                    ->rows(3)
                                                    ->maxLength(500)
                                                    ->helperText('Aparece debajo del nombre en tu catálogo público'),
                                            ]),
                                    ])->columnSpan(1),

                                Group::make()
                                    ->schema([
                                        Section::make('Contacto del Catálogo')
                                            ->description('Cómo te contactan tus clientes')
                                            ->icon('heroicon-o-chat-bubble-left-right')
                                            ->iconColor('success')
                                            ->schema([
                                                TextInput::make('whatsapp_number')
                                                    ->label('WhatsApp de la Tienda')
                                                    ->tel()
                                                    ->maxLength(20)
                                                    ->placeholder('55 1234 5678')
                                                    ->mask('99 9999 9999')
                                                    ->prefixIcon('heroicon-o-phone')
                                                    ->helperText('Los clientes recibirán avisos aquí'),
                                                Placeholder::make('catalog_url')
                                                    ->label('Enlace del Catálogo')
                                                    ->content(function ($record) {
                                                        if (! $record) {
                                                            return 'Guarda la tienda para generar el enlace';
                                                        }

                                                        $url = route('tienda.catalogo', $record->id);

                                                        return new HtmlString(
                                                            "<div style='display:flex;align-items:center;gap:8px;'>
                                                                <code style='font-size:12px;background:#f3f4f6;padding:4px 8px;border-radius:6px;word-break:break-all;'>{$url}</code>
                                                                <button 
                                                                    type='button' 
                                                                    onclick=\"navigator.clipboard.writeText('{$url}'); this.textContent='✓ Copiado'\" 
                                                                    style='font-size:12px;color:#16a34a;font-weight:600;cursor:pointer;background:none;border:none;white-space:nowrap;'
                                                                >Copiar</button>
                                                            </div>"
                                                        );
                                                    })
                                                    ->helperText('Comparte este enlace con tus clientes'),
                                                Placeholder::make('catalog_stats')
                                                    ->label('Productos en Catálogo')
                                                    ->content(function ($record) {
                                                        if (! $record) return '—';

                                                        $total = $record->products()->count();
                                                        $active = $record->products()
                                                            ->where('is_active', true)
                                                            ->where('stock', '>', 0)
                                                            ->count();

                                                        return new HtmlString(
                                                            "<span style='font-size:20px;font-weight:800;color:#16a34a;'>{$active}</span>
                                                             <span style='font-size:13px;color:#6b7280;'>activos de {$total} totales</span>"
                                                        );
                                                    }),
                                            ]),
                                    ])->columnSpan(1),
                            ])->columns(2),

                    ]),
            ]);
    }
}