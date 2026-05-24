<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Domains\Order\Actions\PlaceOrderAction;
use Illuminate\Http\Request;
use Exception;

class OrderController extends Controller
{
    protected $placeOrderAction;

    public function __construct(PlaceOrderAction $placeOrderAction)
    {
        $this->placeOrderAction = $placeOrderAction;
    }

    public function store(Request $request)
    {
        // عمل Validation سريع للمدخلات
        $validated = $request->validate([
            'user_id' => 'required|integer',
            'items' => 'required|array',
            'items.*.product_id' => 'required|integer',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        try {
            // تشغيل الـ Action الخاص بالـ DDD لإنشاء الطلب وحجز المخزون
            $order = $this->placeOrderAction->execute($validated);

            return response()->json([
                'status' => 'success',
                'message' => 'Order placed successfully!',
                'data' => [
                    'order_number' => $order->order_number,
                    'total_amount' => $order->total_amount,
                    'status' => $order->status
                ]
            ], 201);

        } catch (Exception $e) {
            // إرجاع رسالة خطأ محترفة في حال نفاد المخزون (مثلاً خطأ 422)
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 422);
        }
    }
}