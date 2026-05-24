<?php

use App\Http\Controllers\Api\V1\OrderController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/orders', [OrderController::class, 'store'])
         ->middleware(['idempotent', 'throttle:api']);
});