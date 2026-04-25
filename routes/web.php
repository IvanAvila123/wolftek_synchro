<?php

use App\Http\Controllers\WebhookController;
use App\Models\CreditPayment;
use App\Models\Expense;
use App\Models\OnlineOrder;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::get('/', function () {
    /** @var User|null $user */
    $user = Auth::user();

    if (! $user) {
        return redirect('/admin/login');
    }

    if ($user->hasDirectRole(['super_admin'])) {
        return redirect('/superadmin');
    }

    $rolePanel = $user->getRolePanel();

    if ($rolePanel === 'cashier') {
        return redirect('/cashier');
    }

    return redirect('/admin');
});

Route::get('/imprimir-ticket/{sale}', function (Sale $sale) {
    // Cargamos las relaciones, incluyendo al cliente por si le fiamos
    $sale->load('items.product', 'store', 'user', 'customer');
    
    return view('venta', compact('sale'));
})->name('ticket.imprimir');

Route::get('/imprimir-abono/{payment}', function (CreditPayment $payment) {
    $payment->load('customer', 'store', 'user');
    
    return view('abono', compact('payment'));
})->name('abono.imprimir');

Route::get('/imprimir-gasto/{expense}', function (Expense $expense) {
    // Cargamos a la tienda y al cajero que hizo el movimiento
    $expense->load('store', 'user');
    
    return view('gasto', compact('expense'));
})->name('ticket.gasto');

Route::get('/imprimir-etiquetas', function (Request $request) {
    // Recibimos los IDs separados por coma (ej: 1,1,1,1,1)
    $ids = explode(',', $request->query('ids'));
    
    // Traemos los productos de la BD y los organizamos por su ID
    $productosBase = Product::whereIn('id', $ids)->get()->keyBy('id');
    
    // Mapeamos el arreglo original para que se repitan exactamente como se pidieron
    $products = collect($ids)->map(function ($id) use ($productosBase) {
        return $productosBase->get($id);
    })->filter(); // filter() quita los vacíos por si mandan un ID inválido
    
    return view('etiquetas', compact('products'));
})->name('etiquetas.imprimir');

Route::livewire('/tienda/{store}', 'pages::store.catalog')->name('tienda.catalogo');

Route::get('pedido-online/{order}', function (OnlineOrder $order) {
 
 
    return view('pedido-online', compact('order'));
 
})->middleware('auth')->name('pedido-online');

Route::post('/webhook/conekta', [WebhookController::class, 'handleConekta']);
Route::post('/webhook/mercadopago', [WebhookController::class, 'handleMercadoPago']);