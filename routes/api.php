<?php

use App\Http\Controllers\Api\DesktopPosSyncController;
use App\Http\Middleware\AuthenticateDesktopTerminal;
use Illuminate\Support\Facades\Route;

Route::post('/desktop-pos/sync', DesktopPosSyncController::class)
    ->middleware(AuthenticateDesktopTerminal::class)
    ->name('desktop-pos.sync');
