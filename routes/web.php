<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\SettingsController;
use App\Http\Middleware\EnsureActiveBusiness;
use App\Http\Middleware\RequireAdministrator;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => auth()->check() ? redirect()->route('dashboard') : redirect()->route('login'));

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/login', [AuthController::class, 'authenticate'])->middleware('throttle:10,1')->name('login.store');
    Route::get('/forgot-password', [AuthController::class, 'forgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'resetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'updatePassword'])->name('password.update');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware(['auth', EnsureActiveBusiness::class])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/account/password', [AuthController::class, 'editPassword'])->name('account.password.edit');
    Route::put('/account/password', [AuthController::class, 'changePassword'])
        ->middleware('throttle:5,1')->name('account.password.update');

    Route::get('/sales', [PosController::class, 'sales'])->name('sales.index');
    Route::get('/sales/export', [PosController::class, 'exportSales'])->name('sales.export');
    Route::get('/sales/{sale}', [PosController::class, 'showSale'])->name('sales.show');
    Route::get('/sales/{sale}/receipt', [PosController::class, 'receipt'])->name('sales.receipt');
    Route::get('/products', [PosController::class, 'products'])->name('products.index');
    Route::get('/cashiers', [PosController::class, 'cashiers'])->name('cashiers.index');
    Route::get('/terminals', [PosController::class, 'terminals'])->name('terminals.index');
    Route::get('/terminals/{terminal}/sales', [PosController::class, 'terminalSales'])->name('terminals.sales');
    Route::get('/sync-history', [PosController::class, 'syncHistory'])->name('sync.index');
    Route::get('/sync-history/{syncLog}', [PosController::class, 'showSync'])->name('sync.show');
    Route::post('/sync-history/{syncLog}/retry', [PosController::class, 'retrySync'])
        ->middleware(RequireAdministrator::class)->name('sync.retry');
    Route::get('/reports/{type?}', [ReportsController::class, 'index'])->name('reports.index');
    Route::get('/reports/{type}/export', [ReportsController::class, 'export'])->name('reports.export');

    Route::middleware(RequireAdministrator::class)->group(function () {
        Route::get('/terminals/register', [SettingsController::class, 'newTerminal'])->name('terminals.create');
        Route::post('/terminals', [SettingsController::class, 'storeTerminal'])->name('terminals.store');
        Route::patch('/terminals/{terminal}/status', [SettingsController::class, 'toggleTerminal'])->name('terminals.status');
        Route::post('/terminals/{terminal}/rotate-token', [SettingsController::class, 'rotateTerminalToken'])->name('terminals.rotate');
        Route::get('/users', [SettingsController::class, 'users'])->name('users.index');
        Route::post('/users', [SettingsController::class, 'storeUser'])->name('users.store');
        Route::patch('/users/{managedUser}', [SettingsController::class, 'updateUser'])->name('users.update');
        Route::delete('/users/{managedUser}', [SettingsController::class, 'deleteUser'])->name('users.destroy');
        Route::get('/settings', [SettingsController::class, 'editBusiness'])->name('settings.edit');
        Route::put('/settings', [SettingsController::class, 'updateBusiness'])->name('settings.update');
    });
});
