<?php

use App\Http\Controllers\Auth\BranchSelectController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\App\BranchController;
use App\Http\Controllers\App\CategoryController;
use App\Http\Controllers\App\CustomerController;
use App\Http\Controllers\App\DashboardController;
use App\Http\Controllers\App\InventoryController;
use App\Http\Controllers\App\ModifierGroupController;
use App\Http\Controllers\App\ProductController;
use App\Http\Controllers\App\TableController;
use App\Http\Controllers\POS\POSController;
use Illuminate\Support\Facades\Route;

// ── Public ────────────────────────────────────────────────────────────────────
Route::get('/', fn () => redirect()->route('login'));

// ── Auth ──────────────────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/branch-select', [BranchSelectController::class, 'show'])->name('branch.select');
    Route::post('/branch-select', [BranchSelectController::class, 'select'])->name('branch.select.post');
    Route::post('/branch-change', [BranchSelectController::class, 'changeBranch'])->name('branch.change');
});

// ── App (tenant) ──────────────────────────────────────────────────────────────
Route::middleware(['auth', 'tenant.active', 'branch.selected'])
    ->prefix('app')
    ->name('app.')
    ->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Categories
        Route::resource('categories', CategoryController::class)->except(['show']);

        // Branches
        Route::resource('branches', BranchController::class)->except(['show']);

        // Products
        Route::resource('products', ProductController::class)->except(['show']);
        Route::post('products/{product}/variants', [ProductController::class, 'storeVariant'])->name('products.variants.store');
        Route::delete('products/{product}/variants/{variant}', [ProductController::class, 'destroyVariant'])->name('products.variants.destroy');

        // Modifier groups
        Route::resource('modifiers', ModifierGroupController::class)->except(['show']);
        Route::post('modifiers/{modifier}/options', [ModifierGroupController::class, 'storeOption'])->name('modifiers.options.store');
        Route::delete('modifiers/{modifier}/options/{option}', [ModifierGroupController::class, 'destroyOption'])->name('modifiers.options.destroy');

        // Tables
        Route::resource('tables', TableController::class)->except(['show']);
        Route::post('tables/{table}/regenerate-qr', [TableController::class, 'regenerateQr'])->name('tables.regenerate-qr');
        Route::get('tables/{table}/qr', [TableController::class, 'qr'])->name('tables.qr');

        // Inventory
        Route::get('inventory', [InventoryController::class, 'index'])->name('inventory.index');
        Route::match(['POST', 'PATCH'], 'inventory/{inventory}/adjust', [InventoryController::class, 'adjust'])->name('inventory.adjust');

        // Customers
        Route::resource('customers', CustomerController::class)->except(['show']);
    });

// ── POS (Kasir) ───────────────────────────────────────────────────────────────
Route::middleware(['auth', 'tenant.active', 'branch.selected'])
    ->prefix('pos')
    ->name('pos.')
    ->group(function () {
        Route::get('/', [POSController::class, 'index'])->name('index');
        Route::get('/products', [POSController::class, 'products'])->name('products');
        Route::post('/orders', [POSController::class, 'store'])->name('orders.store');
        Route::get('/orders', [POSController::class, 'orders'])->name('orders.index');
        Route::get('/orders/{order}/receipt', [POSController::class, 'receipt'])->name('orders.receipt');
        Route::post('/voucher/validate', [POSController::class, 'validateVoucher'])->name('voucher.validate');
    });

// ── Public QR order page (placeholder — built in Fase 3) ─────────────────────
Route::get('/order/{token}', fn () => abort(404))->name('order.show');

// ── Subscription expired ──────────────────────────────────────────────────────
Route::middleware('auth')->get('/subscription/expired', fn () => view('subscription.expired'))
    ->name('subscription.expired');
