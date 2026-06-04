<?php

use App\Http\Controllers\Api\V1\OrderController;
use App\Domains\Order\Controllers\OrderApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Version 1 (V1)
|--------------------------------------------------------------------------
*/
Route::prefix('v1')->group(function () {

    Route::post('/orders', [OrderController::class, 'store'])
         ->middleware(['idempotent', 'throttle:api']);

    Route::post('/orders/{id}/support', [OrderController::class, 'handleSupportTicket']);

    // 3. جلب قائمة الطلبات (المحسنة من الدومين لمنع تكرار الاستعلامات N+1)
    Route::get('/orders', [OrderApiController::class, 'index']);

    // 4. جلب تفاصيل طلب معين (المسرّعة من الدومين عبر الـ Redis Cache)
    Route::get('/orders/{id}', [OrderApiController::class, 'show']);
});