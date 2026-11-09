<?php

use App\Http\Controllers\Api\DesktopPosSyncController;
use App\Http\Middleware\ResolveDesktopTerminal;
use Illuminate\Support\Facades\Route;

Route::post('/desktop-pos/sync', DesktopPosSyncController::class)
    ->middleware(['throttle:desktop-pos-sync', ResolveDesktopTerminal::class])
    ->name('desktop-pos.sync');
