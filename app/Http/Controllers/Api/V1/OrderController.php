<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Domains\Order\Actions\PlaceOrderAction;
use App\Domains\Order\Actions\HandleCustomerSupportAction;
use Illuminate\Http\Request;
use Exception;

class OrderController extends Controller
{
    protected $placeOrderAction;
    protected $handleSupportAction;

    public function __construct(PlaceOrderAction $placeOrderAction, HandleCustomerSupportAction $handleSupportAction)
    {
        $this->placeOrderAction = $placeOrderAction;
        $this->handleSupportAction = $handleSupportAction;
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

    public function handleSupportTicket(Request $request, $orderId)
    {
        $request->validate(['message' => 'required|string|min:10']);

        $order = \App\Domains\Order\Models\Order::findOrFail($orderId);
        
        // تنفيذ الـ Action واستقبال النتيجة الجاهزة
        $result = $this->handleSupportAction->execute($order, $request->input('message'));

        return response()->json($result, 200);
    }
}