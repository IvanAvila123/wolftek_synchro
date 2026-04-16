<?php

use Livewire\Component;
use Livewire\Attributes\Computed;
use App\Models\Store;
use App\Models\Product;
use App\Models\OnlineOrder;
use App\Models\Promotion;
use App\Models\User;
use Filament\Notifications\Notification;

new class extends Component {
    public Store $store;
    public array $cart = [];

    public bool $showCheckoutModal = false;
    public string $customerName = '';
    public string $customerPhone = '';
    public string $notes = '';
    public bool $orderCompleted = false;
    public ?int $currentOrderId = null;

    // Nuevas propiedades
    public string $search = '';
    public ?int $selectedCategory = null;
    public bool $darkMode = false;

    public function mount(Store $store)
    {
        $this->store = $store;
    }

    #[Computed]
    public function categorias()
    {
        return \App\Models\Category::whereHas('products', function ($q) {
            $q->where('store_id', $this->store->id)
                ->where('is_active', true)
                ->where('stock', '>', 0);
        })->get();
    }

    #[Computed]
    public function productos()
    {
        return Product::where('store_id', $this->store->id)
            ->where('is_active', true)
            ->where('stock', '>', 5)
            ->when(
                $this->search,
                fn($q) =>
                $q->where('name', 'like', '%' . $this->search . '%')
            )
            ->when(
                $this->selectedCategory,
                fn($q) =>
                $q->where('category_id', $this->selectedCategory)
            )
            ->with(['batches' => fn($q) => $q->where('quantity', '>', 0)->orderBy('expiry_date', 'asc'), 'batches.promotions'])
            ->get();
    }

    public function setCategory(?int $id)
    {
        $this->selectedCategory = $this->selectedCategory === $id ? null : $id;
    }

    public function toggleDarkMode()
    {
        $this->darkMode = !$this->darkMode;
    }

    public function addToCart($productId)
    {
        $product = Product::with(['batches' => fn($q) => $q->where('quantity', '>', 0)->orderBy('expiry_date', 'asc'), 'batches.promotions'])->find($productId);
        if (!$product || $product->has_scale) return;

        if (isset($this->cart[$productId])) {
            if ($this->cart[$productId]['qty'] < $product->stock) {
                $this->cart[$productId]['qty']++;
                $this->cart[$productId]['subtotal'] = $this->recalcularSubtotal($this->cart[$productId]);
            }
        } else {
            $item = [
                'id'        => $product->id,
                'name'      => $product->name,
                'price'     => $product->price_sell,
                'qty'       => 1,
                'stock'     => $product->stock,
                'has_scale' => false,
                'unidad'    => $product->unidad,
            ];

            $promo = $product->promocionActivaFefo();
            if ($promo) {
                $calc = $promo->calcularSubtotal(1, $product->price_sell);
                $item['price']          = $calc['price_unit'];
                $item['price_original'] = $product->price_sell;
                $item['promo_label']    = $calc['label'];
                $item['promo_tipo']     = $promo->tipo;
                $item['promo_paga']     = $promo->cantidad_paga;
                $item['promo_lleva']    = $promo->cantidad_lleva;
            }

            $item['subtotal'] = $this->recalcularSubtotal($item);
            $this->cart[$productId] = $item;
        }
    }

    public function recalcularSubtotal(array $item): float
    {
        $qty = $item['qty'];

        if (empty($item['promo_tipo'])) {
            return $qty * $item['price'];
        }

        if ($item['promo_tipo'] === 'nxm') {
            $precio = $item['price_original'] ?? $item['price'];
            $lleva  = $item['promo_lleva'];
            $paga   = $item['promo_paga'];
            return (floor($qty / $lleva) * $paga + fmod($qty, $lleva)) * $precio;
        }

        return $qty * $item['price']; // porcentaje / precio_fijo: price ya tiene el descuento
    }

    // Productos por peso: el cliente elige una porción en gramos
    public function addScaleToCart($productId, $grams)
    {
        $product = Product::find($productId);
        if (!$product || !$product->has_scale) return;

        $qty = round($grams / 1000, 3); // convertir a kg

        $this->cart[$productId] = [
            'id'        => $product->id,
            'name'      => $product->name,
            'price'     => $product->price_sell,   // precio por kg
            'qty'       => $qty,
            'stock'     => $product->stock,
            'has_scale' => true,
            'unidad'    => $product->unidad,
            'grams'     => $grams,
        ];
    }

    public function decrementQty($productId)
    {
        if (isset($this->cart[$productId]) && empty($this->cart[$productId]['has_scale'])) {
            if ($this->cart[$productId]['qty'] > 1) {
                $this->cart[$productId]['qty']--;
                $this->cart[$productId]['subtotal'] = $this->recalcularSubtotal($this->cart[$productId]);
            } else {
                unset($this->cart[$productId]);
            }
            if (empty($this->cart)) {
                $this->showCheckoutModal = false;
            }
        }
    }

    public function removeFromCart($productId)
    {
        unset($this->cart[$productId]);
        if (empty($this->cart)) {
            $this->showCheckoutModal = false;
        }
    }

    #[Computed]
    public function cartTotal()
    {
        return collect($this->cart)->sum(fn($item) => $item['subtotal'] ?? ($item['price'] * $item['qty']));
    }

    #[Computed]
    public function cartCount()
    {
        return collect($this->cart)->sum('qty');
    }

    public function proceedToCheckout()
    {
        $this->showCheckoutModal = true;
    }

    public function submitOrder()
{
    $this->validate([
        'customerName'  => 'required|min:3',
        'customerPhone' => 'required|min:10',
    ]);

    $pedido = OnlineOrder::create([
        'store_id'       => $this->store->id,
        'customer_name'  => $this->customerName,
        'customer_phone' => $this->customerPhone,
        'notes'          => $this->notes,
        'total'          => $this->cartTotal,
        'cart_items'     => json_encode($this->cart),
        'status'         => 'pending',
    ]);


    $usersToNotify = \App\Models\User::whereHas('store', function($q) {
        $q->where('id', $this->store->id);
    })->get();

    Notification::make()
        ->title('¡Pedido Nuevo!')
        ->body("{$this->customerName} envió un pedido de $" . number_format($this->cartTotal, 2))
        ->icon('heroicon-o-shopping-cart')
        ->color('success')
        ->success()
        ->sendToDatabase($usersToNotify);
    // 👆 HASTA AQUÍ 👆

    $this->currentOrderId    = $pedido->id;
    $this->showCheckoutModal = false;
    $this->orderCompleted    = true;
    $this->cart              = [];
}

    public function cancelOrder()
    {
        if ($this->currentOrderId) {
            $pedido = OnlineOrder::find($this->currentOrderId);

            if ($pedido && $pedido->status === 'pending') {
                $pedido->delete();
            }
        }

        $this->orderCompleted  = false;
        $this->currentOrderId  = null;
    }
};
?>

<div data-catalog data-theme="{{ $darkMode ? 'dark' : 'light' }}" style="padding-bottom: 112px; background: var(--c-bg); min-height: 100vh; transition: background 0.3s;">

    {{-- ===== FUENTES Y ESTILOS ===== --}}
    <style>
        @@import url('https://fonts.googleapis.com/css2?family=Nunito:wght@@400;600;700;800;900&display=swap');

        [data-catalog] * {
            font-family: 'Nunito', sans-serif;
            box-sizing: border-box;
        }

        /* ── Paleta LIGHT — tonos claros y cálidos ── */
        [data-catalog][data-theme="light"] {
            --c-bg: #f9fafb;
            --c-surface: #ffffff;
            --c-primary: #f59e0b;
            --c-primary-dark: #d97706;
            --c-primary-light: #fef3c7;
            --c-accent: #f97316;
            --c-text: #1f2937;
            --c-muted: #6b7280;
            --c-border: #e5e7eb;
            --c-red: #ef4444;
            --c-input-bg: #fafafa;
            --c-card-hover: rgba(0, 0, 0, 0.08);
            --c-modal-bg: #ffffff;
            --c-hero-start: #f59e0b;
            --c-hero-mid: #f97316;
            --c-hero-end: #fb923c;
            --c-wave-fill: #f9fafb;
            --c-bar-gradient: linear-gradient(to top, #f9fafb 80%, transparent);
            --c-shadow-primary: rgba(245, 158, 11, 0.35);
            --c-focus-ring: rgba(245, 158, 11, 0.15);
            --r: 14px;
        }

        /* ── Paleta DARK — azul marino y negro ── */
        [data-catalog][data-theme="dark"] {
            --c-bg: #0a0e1a;
            --c-surface: #111827;
            --c-primary: #60a5fa;
            --c-primary-dark: #3b82f6;
            --c-primary-light: #172033;
            --c-accent: #f59e0b;
            --c-text: #e5e7eb;
            --c-muted: #9ca3af;
            --c-border: #1e2a42;
            --c-red: #f87171;
            --c-input-bg: #111827;
            --c-card-hover: rgba(96, 165, 250, 0.06);
            --c-modal-bg: #111827;
            --c-hero-start: #0a0e1a;
            --c-hero-mid: #0f1729;
            --c-hero-end: #162036;
            --c-wave-fill: #0a0e1a;
            --c-bar-gradient: linear-gradient(to top, #0a0e1a 80%, transparent);
            --c-shadow-primary: rgba(96, 165, 250, 0.3);
            --c-focus-ring: rgba(96, 165, 250, 0.15);
            --r: 14px;
        }

        /* ── Hero header ── */
        [data-catalog] .hero {
            background: linear-gradient(135deg, var(--c-hero-start) 0%, var(--c-hero-mid) 50%, var(--c-hero-end) 100%);
            padding: 28px 20px 56px;
            position: relative;
            overflow: hidden;
        }

        [data-catalog] .hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(ellipse at 80% 20%, rgba(255, 255, 255, 0.07) 0%, transparent 60%);
        }

        [data-catalog] .hero-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 10px;
            position: relative;
            z-index: 2;
        }

        [data-catalog] .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.25);
            color: #fff;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            padding: 4px 12px;
            border-radius: 99px;
        }

        [data-catalog] .hero-name {
            font-size: 30px;
            font-weight: 900;
            color: #fff;
            line-height: 1.1;
            margin-bottom: 4px;
        }

        [data-catalog] .hero-sub {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.65);
        }

        [data-catalog] .hero-logo {
            width: 72px;
            height: 72px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            margin-bottom: 10px;
            position: relative;
            z-index: 2;
        }

        [data-catalog] .hero-emoji {
            font-size: 48px;
            margin-bottom: 8px;
            position: relative;
            z-index: 2;
        }

        [data-catalog] .hero-center {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            position: relative;
            z-index: 2;
        }

        [data-catalog] .hero-wave {
            position: absolute;
            bottom: -1px;
            left: 0;
            width: 100%;
            height: 32px;
        }

        /* ── Botón dark mode ── */
        [data-catalog] .btn-theme {
            width: 36px;
            height: 36px;
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.25);
            border-radius: 99px;
            color: #fff;
            font-size: 18px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.15s;
            flex-shrink: 0;
        }

        [data-catalog] .btn-theme:hover {
            background: rgba(255, 255, 255, 0.25);
        }

        /* ── Buscador ── */
        [data-catalog] .search-wrap {
            position: relative;
            margin-bottom: 14px;
        }

        [data-catalog] .search-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 16px;
            color: var(--c-muted);
            pointer-events: none;
        }

        [data-catalog] .search-input {
            width: 100%;
            padding: 12px 14px 12px 42px;
            border: 1.5px solid var(--c-border);
            border-radius: 12px;
            font-family: 'Nunito', sans-serif;
            font-size: 14px;
            font-weight: 600;
            color: var(--c-text);
            background: var(--c-surface);
            outline: none;
            transition: border-color 0.15s, box-shadow 0.15s;
        }

        [data-catalog] .search-input::placeholder {
            color: var(--c-muted);
        }

        [data-catalog] .search-input:focus {
            border-color: var(--c-primary);
            box-shadow: 0 0 0 3px var(--c-focus-ring);
        }

        /* ── Tags de categoría ── */
        [data-catalog] .category-scroll {
            display: flex;
            gap: 8px;
            overflow-x: auto;
            padding-bottom: 4px;
            margin-bottom: 16px;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }

        [data-catalog] .category-scroll::-webkit-scrollbar {
            display: none;
        }

        [data-catalog] .category-tag {
            flex-shrink: 0;
            padding: 7px 16px;
            border-radius: 99px;
            border: 1.5px solid var(--c-border);
            background: var(--c-surface);
            color: var(--c-muted);
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.15s;
            white-space: nowrap;
            font-family: 'Nunito', sans-serif;
        }

        [data-catalog] .category-tag:hover {
            border-color: var(--c-primary);
            color: var(--c-primary);
        }

        [data-catalog] .category-tag.active {
            background: var(--c-primary);
            border-color: var(--c-primary);
            color: #fff;
        }

        /* ── Tarjeta de producto ── */
        [data-catalog] .product-card {
            background: var(--c-surface);
            border: 1px solid var(--c-border);
            border-radius: var(--r);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            transition: box-shadow 0.2s, transform 0.2s;
        }

        [data-catalog] .product-card:hover {
            box-shadow: 0 8px 24px var(--c-card-hover);
            transform: translateY(-2px);
        }

        [data-catalog] .product-img {
            background: linear-gradient(135deg, var(--c-primary-light) 0%, var(--c-bg) 100%);
            height: 110px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 42px;
            position: relative;
        }

        [data-catalog] .product-stock-pill {
            position: absolute;
            top: 8px;
            right: 8px;
            background: var(--c-surface);
            border: 1px solid var(--c-border);
            border-radius: 99px;
            font-size: 10px;
            font-weight: 700;
            color: var(--c-muted);
            padding: 2px 8px;
        }

        [data-catalog] .product-body {
            padding: 10px 12px 12px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        [data-catalog] .product-name {
            font-size: 13px;
            font-weight: 800;
            color: var(--c-text);
            line-height: 1.3;
            margin-bottom: 2px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        [data-catalog] .product-price {
            font-size: 19px;
            font-weight: 900;
            color: var(--c-primary);
            margin-top: auto;
            padding-top: 6px;
        }

        [data-catalog] .product-price .currency {
            font-size: 12px;
            font-weight: 700;
            vertical-align: super;
            margin-right: 1px;
        }

        [data-catalog] .promo-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: #ef4444;
            color: #fff;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            padding: 2px 8px;
            border-radius: 99px;
            margin-bottom: 4px;
        }

        [data-catalog] .price-original {
            font-size: 12px;
            color: var(--c-muted);
            text-decoration: line-through;
            margin-right: 4px;
        }

        /* ── Controles de carrito ── */
        [data-catalog] .btn-add {
            width: 32px;
            height: 32px;
            background: var(--c-primary);
            color: #fff;
            border: none;
            border-radius: 99px;
            font-size: 20px;
            font-weight: 900;
            line-height: 1;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.15s, transform 0.1s;
            flex-shrink: 0;
        }

        [data-catalog] .btn-add:hover {
            background: var(--c-primary-dark);
        }

        [data-catalog] .btn-add:active {
            transform: scale(0.92);
        }

        [data-catalog] .btn-add:disabled {
            background: #d1d5db;
            cursor: not-allowed;
        }

        [data-catalog] .qty-control {
            display: flex;
            align-items: center;
            gap: 8px;
            background: var(--c-primary-light);
            border-radius: 99px;
            padding: 4px 10px;
        }

        [data-catalog] .qty-control button {
            width: 22px;
            height: 22px;
            border: none;
            background: transparent;
            font-size: 16px;
            font-weight: 900;
            cursor: pointer;
            color: var(--c-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 99px;
            transition: background 0.1s;
        }

        [data-catalog] .qty-control button:hover {
            background: rgba(255, 255, 255, 0.15);
        }

        [data-catalog] .qty-control .qty-num {
            font-size: 14px;
            font-weight: 900;
            color: var(--c-primary);
            min-width: 18px;
            text-align: center;
        }

        /* ── Barra flotante del carrito ── */
        [data-catalog] .cart-bar {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 40;
            padding: 12px 16px 20px;
            background: var(--c-bar-gradient);
        }

        [data-catalog] .cart-btn {
            width: 100%;
            max-width: 480px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: var(--c-primary);
            color: #fff;
            border: none;
            border-radius: var(--r);
            padding: 14px 20px;
            cursor: pointer;
            font-family: 'Nunito', sans-serif;
            transition: background 0.15s, transform 0.1s;
            box-shadow: 0 4px 20px var(--c-shadow-primary);
        }

        [data-catalog] .cart-btn:hover {
            background: var(--c-primary-dark);
        }

        [data-catalog] .cart-btn:active {
            transform: scale(0.98);
        }

        [data-catalog] .cart-btn-label {
            font-size: 15px;
            font-weight: 800;
        }

        [data-catalog] .cart-btn-badge {
            background: rgba(255, 255, 255, 0.25);
            border-radius: 99px;
            padding: 2px 10px;
            font-size: 13px;
            font-weight: 700;
        }

        [data-catalog] .cart-btn-total {
            font-size: 17px;
            font-weight: 900;
        }

        /* ── Modal ── */
        [data-catalog] .modal-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.45);
            z-index: 50;
            display: flex;
            align-items: flex-end;
            justify-content: center;
            padding: 0;
            animation: catalogFadeIn .2s ease;
        }

        [data-catalog] .modal-sheet {
            background: var(--c-modal-bg);
            border-radius: 22px 22px 0 0;
            width: 100%;
            max-width: 520px;
            max-height: 92vh;
            overflow-y: auto;
            animation: catalogSlideUp .25s ease;
        }

        @@keyframes catalogFadeIn {
            from {
                opacity: 0
            }

            to {
                opacity: 1
            }
        }

        @@keyframes catalogSlideUp {
            from {
                transform: translateY(100px);
                opacity: 0
            }

            to {
                transform: translateY(0);
                opacity: 1
            }
        }

        [data-catalog] .modal-handle {
            width: 40px;
            height: 4px;
            background: var(--c-border);
            border-radius: 99px;
            margin: 12px auto 0;
        }

        [data-catalog] .modal-header {
            padding: 16px 20px 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid var(--c-border);
        }

        [data-catalog] .modal-title {
            font-size: 18px;
            font-weight: 900;
            color: var(--c-text);
        }

        [data-catalog] .modal-close {
            width: 32px;
            height: 32px;
            background: var(--c-primary-light);
            border: none;
            border-radius: 99px;
            font-size: 16px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--c-muted);
            transition: background .15s;
        }

        [data-catalog] .modal-close:hover {
            background: var(--c-border);
        }

        /* ── Items en modal ── */
        [data-catalog] .cart-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 0;
            border-bottom: 1px solid var(--c-border);
        }

        [data-catalog] .cart-item-icon {
            width: 40px;
            height: 40px;
            background: var(--c-primary-light);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        }

        [data-catalog] .cart-item-name {
            font-size: 13px;
            font-weight: 700;
            color: var(--c-text);
        }

        [data-catalog] .cart-item-price {
            font-size: 12px;
            color: var(--c-muted);
            margin-top: 1px;
        }

        [data-catalog] .cart-item-subtotal {
            margin-left: auto;
            font-size: 14px;
            font-weight: 800;
            color: var(--c-primary);
            flex-shrink: 0;
        }

        /* ── Inputs del modal ── */
        [data-catalog] .field-label {
            display: block;
            font-size: 12px;
            font-weight: 800;
            color: var(--c-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }

        [data-catalog] .field-input {
            width: 100%;
            padding: 11px 14px;
            border: 1.5px solid var(--c-border);
            border-radius: 10px;
            font-family: 'Nunito', sans-serif;
            font-size: 14px;
            font-weight: 600;
            color: var(--c-text);
            background: var(--c-input-bg);
            outline: none;
            transition: border-color 0.15s, box-shadow 0.15s;
        }

        [data-catalog] .field-input:focus {
            border-color: var(--c-primary);
            box-shadow: 0 0 0 3px var(--c-focus-ring);
            background: var(--c-surface);
        }

        [data-catalog] .field-error {
            font-size: 11px;
            color: var(--c-red);
            font-weight: 700;
            margin-top: 4px;
        }

        /* ── Botón submit ── */
        [data-catalog] .btn-submit {
            width: 100%;
            padding: 15px;
            background: var(--c-primary);
            color: #fff;
            border: none;
            border-radius: var(--r);
            font-family: 'Nunito', sans-serif;
            font-size: 16px;
            font-weight: 900;
            cursor: pointer;
            transition: background .15s, transform .1s;
            box-shadow: 0 4px 14px var(--c-shadow-primary);
        }

        [data-catalog] .btn-submit:hover {
            background: var(--c-primary-dark);
        }

        [data-catalog] .btn-submit:active {
            transform: scale(0.98);
        }

        /* ── Pantalla de éxito ── */
        [data-catalog] .success-screen {
            min-height: 70vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 40px 24px;
            animation: catalogFadeIn .4s ease;
        }

        [data-catalog] .success-icon {
            width: 88px;
            height: 88px;
            background: var(--c-primary-light);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 24px;
            font-size: 40px;
        }

        [data-catalog] .success-title {
            font-size: 28px;
            font-weight: 900;
            color: var(--c-text);
            margin-bottom: 8px;
        }

        [data-catalog] .success-sub {
            font-size: 14px;
            color: var(--c-muted);
            max-width: 300px;
            line-height: 1.6;
            margin-bottom: 32px;
        }

        [data-catalog] .success-actions .btn-secondary {
            padding: 12px 28px;
            background: var(--c-primary-light);
            color: var(--c-primary);
            border: none;
            border-radius: 99px;
            font-family: 'Nunito', sans-serif;
            font-size: 14px;
            font-weight: 800;
            cursor: pointer;
            transition: background .15s;
            width: 100%;
        }

        [data-catalog] .btn-secondary:hover {
            background: var(--c-border);
        }

        /* ── Acciones en pantalla de éxito ── */
        [data-catalog] .success-actions {
            display: flex;
            flex-direction: column;
            gap: 10px;
            align-items: center;
            width: 100%;
            max-width: 280px;
        }

        [data-catalog] .btn-cancel {
            padding: 10px 28px;
            background: transparent;
            color: var(--c-red);
            border: 1.5px solid var(--c-red);
            border-radius: 99px;
            font-family: 'Nunito', sans-serif;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition: background .15s, color .15s;
            width: 100%;
        }

        [data-catalog] .btn-cancel:hover {
            background: var(--c-red);
            color: #fff;
        }

        /* ── Total pill ── */
        [data-catalog] .total-pill {
            background: var(--c-primary-light);
            border-radius: var(--r);
            padding: 14px 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        [data-catalog] .total-pill-label {
            font-size: 13px;
            font-weight: 800;
            color: var(--c-primary);
        }

        [data-catalog] .total-pill-amount {
            font-size: 22px;
            font-weight: 900;
            color: var(--c-primary);
        }

        /* ── Botones de porción (productos por peso) ── */
        [data-catalog] .portion-wrap {
            display: flex;
            flex-direction: column;
            gap: 4px;
            margin-top: 8px;
        }

        [data-catalog] .portion-selected {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: var(--c-primary-light);
            border-radius: 8px;
            padding: 4px 8px;
            font-size: 12px;
            font-weight: 800;
            color: var(--c-primary);
        }

        [data-catalog] .portion-selected button {
            background: none;
            border: none;
            color: var(--c-primary);
            cursor: pointer;
            font-size: 14px;
            font-weight: 900;
            line-height: 1;
            padding: 0 2px;
        }

        [data-catalog] .portion-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 4px;
        }

        [data-catalog] .portion-btn {
            padding: 5px 2px;
            border: 1.5px solid var(--c-border);
            border-radius: 8px;
            background: var(--c-surface);
            color: var(--c-muted);
            font-family: 'Nunito', sans-serif;
            font-size: 11px;
            font-weight: 800;
            cursor: pointer;
            transition: all 0.15s;
            text-align: center;
        }

        [data-catalog] .portion-btn:hover,
        [data-catalog] .portion-btn.active {
            border-color: var(--c-primary);
            background: var(--c-primary);
            color: #fff;
        }

        [data-catalog] .scale-price-hint {
            font-size: 10px;
            color: var(--c-muted);
            font-weight: 600;
        }

        /* ── Empty / no results ── */
        [data-catalog] .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--c-muted);
        }

        [data-catalog] .empty-state .icon {
            font-size: 48px;
            margin-bottom: 12px;
        }

        [data-catalog] .empty-state p {
            font-size: 14px;
            font-weight: 600;
        }

        /* ── Resultado count ── */
        [data-catalog] .result-count {
            font-size: 12px;
            font-weight: 700;
            color: var(--c-muted);
            margin-bottom: 12px;
        }

        /* ── Contenedor y grid responsive ── */
        [data-catalog] .catalog-container {
            max-width: 640px;
            margin: 0 auto;
            padding: 20px 14px 0;
        }

        [data-catalog] .product-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }

        /* ── Tablet (≥768px) ── */
        @@media (min-width: 768px) {
            [data-catalog] .catalog-container {
                max-width: 100%;
                padding: 24px 28px 0;
            }

            [data-catalog] .product-grid {
                grid-template-columns: repeat(3, 1fr);
                gap: 16px;
            }

            [data-catalog] .search-input {
                max-width: 400px;
            }

            [data-catalog] .cart-btn {
                max-width: 600px;
            }
        }

        /* ── Desktop (≥1024px) ── */
        @@media (min-width: 1024px) {
            [data-catalog] .catalog-container {
                padding: 28px 40px 0;
            }

            [data-catalog] .product-grid {
                grid-template-columns: repeat(4, 1fr);
                gap: 18px;
            }

            [data-catalog] .cart-btn {
                max-width: 700px;
            }

            [data-catalog] .hero {
                padding: 36px 40px 52px;
            }

            [data-catalog] .hero-name {
                font-size: 38px;
            }
        }

        /* ── Desktop grande (≥1440px) ── */
        @@media (min-width: 1440px) {
            [data-catalog] .catalog-container {
                padding: 32px 60px 0;
            }

            [data-catalog] .product-grid {
                grid-template-columns: repeat(5, 1fr);
                gap: 20px;
            }
        }
    </style>

    {{-- ======================================================
         PANTALLA DE ÉXITO
    ====================================================== --}}
    @if($orderCompleted)
    <div class="success-screen">
        <div class="success-icon">✅</div>
        <div class="success-title">¡Pedido enviado!</div>
        <p class="success-sub">
            La tienda recibió tu pedido. En breve te contactarán por WhatsApp para confirmar.
        </p>
        <div class="success-actions">
            <button wire:click="$set('orderCompleted', false)" class="btn-secondary">
                ← Hacer otro pedido
            </button>
            <button wire:click="cancelOrder" wire:confirm="¿Seguro que quieres cancelar tu pedido?" class="btn-cancel">
                Cancelar pedido
            </button>
        </div>
    </div>

    @else

    {{-- ======================================================
             HERO — Encabezado de la tienda
        ====================================================== --}}
    <div class="hero">
        <div class="hero-top">
            <div class="hero-badge">🏪 Catálogo en línea</div>
            <button wire:click="toggleDarkMode" class="btn-theme" title="Cambiar tema">
                @if($darkMode) ☀️ @else 🌙 @endif
            </button>
        </div>

        <div class="hero-center">
            @if($store->logo)
            <img src="{{ asset('storage/' . $store->logo) }}" alt="Logo {{ $store->name }}" class="hero-logo">
            @else
            <div class="hero-emoji">🏪</div>
            @endif
            <div class="hero-name">{{ $store->name }}</div>
            <div class="hero-sub">{{ $store->catalog_description ?? 'Elige tus productos y haz tu pedido fácil y rápido' }}</div>
        </div>

        <svg class="hero-wave" viewBox="0 0 1440 32" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
            <path d="M0 32L60 26.7C120 21.3 240 10.7 360 8C480 5.3 600 10.7 720 16C840 21.3 960 26.7 1080 26.7C1200 26.7 1320 21.3 1380 18.7L1440 16V32H0Z" fill="var(--c-wave-fill)" />
        </svg>
    </div>

    {{-- ======================================================
             BUSCADOR + CATEGORÍAS + GRID
        ====================================================== --}}
    <div class="catalog-container">

        {{-- Buscador --}}
        <div class="search-wrap">
            <span class="search-icon">🔍</span>
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                class="search-input"
                placeholder="Buscar producto...">
        </div>

        {{-- Tags de categoría --}}
        @if($this->categorias->count() > 0)
        <div class="category-scroll">
            <button
                wire:click="setCategory(null)"
                class="category-tag {{ is_null($selectedCategory) ? 'active' : '' }}">
                Todos
            </button>
            @foreach($this->categorias as $cat)
            <button
                wire:click="setCategory({{ $cat->id }})"
                class="category-tag {{ $selectedCategory === $cat->id ? 'active' : '' }}">
                {{ $cat->name }}
            </button>
            @endforeach
        </div>
        @endif

        {{-- Contador de resultados --}}
        @if($search || $selectedCategory)
        <div class="result-count">
            {{ $this->productos->count() }} producto{{ $this->productos->count() !== 1 ? 's' : '' }} encontrado{{ $this->productos->count() !== 1 ? 's' : '' }}
        </div>
        @endif

        {{-- Grid de productos --}}
        <div class="product-grid">
            @forelse($this->productos as $producto)
            <div class="product-card" wire:key="product-{{ $producto->id }}">

                <div class="product-img">
                    🛒
                    <span class="product-stock-pill">{{ $producto->stock }} pz</span>
                </div>

                <div class="product-body">
                    <div class="product-name">{{ $producto->name }}</div>

                    @if($producto->has_scale)
                    {{-- ── Producto por peso ── --}}
                    @php $cartGrams = isset($cart[$producto->id]) ? (int)($cart[$producto->id]['qty'] * 1000) : 0; @endphp
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 6px;">
                        <div>
                            <div class="product-price">
                                <span class="currency">$</span>{{ number_format($producto->price_sell, 2) }}
                            </div>
                            <div class="scale-price-hint">por {{ $producto->unidad }}</div>
                        </div>
                        @if($cartGrams > 0)
                        <span style="font-size:11px;font-weight:800;color:var(--c-primary);background:var(--c-primary-light);border-radius:99px;padding:3px 10px;">
                            {{ $cartGrams >= 1000 ? number_format($cartGrams/1000, 3).' kg' : $cartGrams.' g' }}
                        </span>
                        @endif
                    </div>
                    <div class="portion-wrap">
                        @if($cartGrams > 0)
                        <div class="portion-selected">
                            <span>Elegido: {{ $cartGrams >= 1000 ? number_format($cartGrams/1000,3).' kg' : $cartGrams.' g' }} — ${{ number_format($cart[$producto->id]['price'] * $cart[$producto->id]['qty'], 2) }}</span>
                            <button wire:click="removeFromCart({{ $producto->id }})" title="Quitar">✕</button>
                        </div>
                        @endif
                        <div class="portion-grid">
                            @foreach([100 => '100g', 250 => '¼ kg', 500 => '½ kg', 1000 => '1 kg'] as $grams => $label)
                            <button
                                wire:click="addScaleToCart({{ $producto->id }}, {{ $grams }})"
                                class="portion-btn {{ $cartGrams === $grams ? 'active' : '' }}"
                                title="${{ number_format($producto->price_sell * $grams / 1000, 2) }}">{{ $label }}</button>
                            @endforeach
                        </div>
                    </div>
                    @else
                    {{-- ── Producto normal ── --}}
                    @php $promoCard = $producto->promocionActivaFefo(); @endphp
                    @if($promoCard)
                    <div class="promo-badge">🏷 {{ $promoCard->nombre }}</div>
                    @endif
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 8px;">
                        <div>
                            @if($promoCard)
                            <div style="display:flex;align-items:baseline;gap:4px;">
                                <span class="price-original">${{ number_format($producto->price_sell, 2) }}</span>
                                @php $calcCard = $promoCard->calcularSubtotal(1, $producto->price_sell); @endphp
                                <div class="product-price" style="padding-top:0;">
                                    <span class="currency">$</span>{{ number_format($calcCard['price_unit'], 2) }}
                                </div>
                            </div>
                            @if($promoCard->tipo === 'nxm')
                            <div style="font-size:10px;color:var(--c-muted);font-weight:700;">{{ $calcCard['label'] }}</div>
                            @endif
                            @else
                            <div class="product-price">
                                <span class="currency">$</span>{{ number_format($producto->price_sell, 2) }}
                            </div>
                            @endif
                        </div>

                        @if(isset($cart[$producto->id]))
                        <div class="qty-control">
                            <button wire:click="decrementQty({{ $producto->id }})">−</button>
                            <span class="qty-num">{{ $cart[$producto->id]['qty'] }}</span>
                            <button wire:click="addToCart({{ $producto->id }})"
                                @if($cart[$producto->id]['qty'] >= $producto->stock) disabled style="opacity:0.4;cursor:not-allowed;" @endif>
                                +
                            </button>
                        </div>
                        @else
                        <button class="btn-add" wire:click="addToCart({{ $producto->id }})">+</button>
                        @endif
                    </div>
                    @endif
                </div>

            </div>
            @empty
            <div class="empty-state" style="grid-column: 1/-1;">
                <div class="icon">🛍️</div>
                @if($search || $selectedCategory)
                <p>No se encontraron productos con ese filtro.</p>
                <button
                    wire:click="$set('search', ''); $set('selectedCategory', null)"
                    style="margin-top: 12px; background: none; border: none; color: var(--c-primary); font-weight: 700; cursor: pointer; font-family: 'Nunito', sans-serif; font-size: 13px;">
                    Limpiar filtros
                </button>
                @else
                <p>Aún no hay productos disponibles.</p>
                @endif
            </div>
            @endforelse
        </div>
    </div>

    @endif

    {{-- ======================================================
         BARRA FLOTANTE DEL CARRITO
    ====================================================== --}}
    @if($this->cartCount > 0 && !$orderCompleted)
    <div class="cart-bar">
        <button class="cart-btn" wire:click="proceedToCheckout">
            <div style="display:flex; align-items:center; gap:8px;">
                <span class="cart-btn-label">Ver pedido</span>
                <span class="cart-btn-badge">{{ $this->cartCount }}</span>
            </div>
            <span class="cart-btn-total">${{ number_format($this->cartTotal, 2) }}</span>
        </button>
    </div>
    @endif

    {{-- ======================================================
         MODAL DE CHECKOUT (bottom sheet)
    ====================================================== --}}
    @if($showCheckoutModal)
    <div class="modal-backdrop" wire:click.self="$set('showCheckoutModal', false)">
        <div class="modal-sheet">

            <div class="modal-handle"></div>

            <div class="modal-header">
                <span class="modal-title">Tu pedido</span>
                <button class="modal-close" wire:click="$set('showCheckoutModal', false)">✕</button>
            </div>

            <div style="padding: 16px 20px 28px;">

                <div style="margin-bottom: 16px;">
                    @foreach($cart as $item)
                    <div class="cart-item">
                        <div class="cart-item-icon">🛒</div>
                        <div>
                            <div class="cart-item-name">{{ $item['name'] }}</div>
                            @if(!empty($item['has_scale']))
                            @php $g = (int)($item['qty'] * 1000); @endphp
                            <div class="cart-item-price">
                                {{ $g >= 1000 ? number_format($item['qty'], 3).' kg' : $g.' g' }}
                                × ${{ number_format($item['price'], 2) }}/{{ $item['unidad'] ?? 'kg' }}
                            </div>
                            @else
                            <div class="cart-item-price">{{ $item['qty'] }} × ${{ number_format($item['price'], 2) }}</div>
                            @if(!empty($item['promo_label']))
                            <div style="font-size:10px;color:#ef4444;font-weight:800;">🏷 {{ $item['promo_label'] }}</div>
                            @endif
                            @endif
                        </div>
                        <div class="cart-item-subtotal">${{ number_format($item['subtotal'] ?? ($item['price'] * $item['qty']), 2) }}</div>
                    </div>
                    @endforeach
                </div>

                <div class="total-pill">
                    <span class="total-pill-label">Total a pagar</span>
                    <span class="total-pill-amount">${{ number_format($this->cartTotal, 2) }}</span>
                </div>

                <form wire:submit="submitOrder">
                    <div style="display: flex; flex-direction: column; gap: 14px; margin-bottom: 20px;">

                        <div>
                            <label class="field-label">Tu nombre</label>
                            <input type="text" wire:model="customerName"
                                class="field-input"
                                placeholder="Ej. Juan Pérez">
                            @error('customerName')
                            <div class="field-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="field-label">WhatsApp de contacto</label>
                            <input type="tel" wire:model="customerPhone"
                                class="field-input"
                                placeholder="Ej. 5512345678">
                            @error('customerPhone')
                            <div class="field-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="field-label">Notas (opcional)</label>
                            <textarea wire:model="notes" rows="2"
                                class="field-input"
                                style="resize: none;"
                                placeholder="Ej. Paso en 20 min, tengo cambio..."></textarea>
                        </div>
                    </div>

                    <button type="submit" class="btn-submit">
                        Enviar pedido →
                    </button>
                </form>

            </div>
        </div>
    </div>
    @endif

</div>