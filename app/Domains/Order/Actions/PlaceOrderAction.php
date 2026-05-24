<?php

namespace App\Domains\Order\Actions;

use App\Domains\Order\Models\Order;
use App\Domains\Inventory\Actions\ReserveStockAction;
use App\Domains\Inventory\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Exception;

class PlaceOrderAction
{
    protected $reserveStockAction;

    // حقن الـ ReserveStockAction الخاص بدومين المخزون عبر الـ Constructor (Dependency Injection)
    public function __construct(ReserveStockAction $reserveStockAction)
    {
        $this->reserveStockAction = $reserveStockAction;
    }

    public function execute(array $data): Order
    {
        // فتح Database Transaction لضمان الأمان الذري للبيانات (All or Nothing)
        return DB::transaction(function () use ($data) {
            
            // 1. حساب السعر الإجمالي وحجز المخزون لكل منتج
            $totalAmount = 0;
            $reservedItems = [];

            foreach ($data['items'] as $item) {
                // استدعاء دومين المخزون لحجز الكمية بأمان
                $product = $this->reserveStockAction->execute($item['product_id'], $item['quantity']);
                
                $itemPrice = $product->price * $item['quantity'];
                $totalAmount += $itemPrice;

                $reservedItems[] = [
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $product->price
                ];
            }

            // 2. إنشاء الطلب الرئيسي في قاعدة البيانات
            $order = Order::create([
                'user_id' => $data['user_id'],
                'order_number' => 'ORD-' . strtoupper(Str::random(10)),
                'total_amount' => $totalAmount,
                'status' => 'pending'
            ]);

            // 3. حفظ تفاصيل المنتجات داخل الطلب
            foreach ($reservedItems as $reservedItem) {
                $order->items()->create($reservedItem);
            }

            return $order;
        });
    }
}