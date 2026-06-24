<?php

use App\Http\Controllers\Auth\BranchSelectController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\App\DashboardController;
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

    // Branch selection (owner who hasn't picked a branch yet)
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
    });

// ── Subscription expired ──────────────────────────────────────────────────────
Route::middleware('auth')->get('/subscription/expired', function () {
    return view('subscription.expired');
})->name('subscription.expired');
