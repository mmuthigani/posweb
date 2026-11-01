<?php

use App\Http\Controllers\Api\DesktopPosSyncController;
use App\Http\Middleware\ResolveDesktopTerminal;
use Illuminate\Support\Facades\Route;

Route::post('/desktop-pos/sync', DesktopPosSyncController::class)
    ->middleware([ResolveDesktopTerminal::class, 'throttle:120,1'])
    ->name('desktop-pos.sync');
