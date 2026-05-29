<?php

namespace Tests\Unit\Domains\Order;

use Tests\TestCase; // use PHPUnit\Framework\TestCase;
use App\Domains\Inventory\Models\Product;
use App\Domains\Order\Actions\PlaceOrderAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Exception;

class PlaceOrderActionTest extends TestCase
{
    use RefreshDatabase; // يقوم بتنظيف قاعدة البيانات تلقائياً قبل كل اختبار

    public function test_it_can_place_an_order_successfully_and_deduct_stock()
    {
        // 1. تجهيز البيانات (Arrange)
        $product = Product::create([
            'name' => 'Test Product',
            'sku' => 'TEST-001',
            'price' => 100.00,
            'stock' => 5,
            'version' => 1
        ]);

        $data = [
            'user_id' => 1,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 2]
            ]
        ];

        // 2. تشغيل الـ Action الفعلي (Act)
        $action = app(PlaceOrderAction::class);
        $order = $action->execute($data);

        // 3. التحقق من النتائج (Assert)
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'total_amount' => 200.00,
            'status' => 'pending'
        ]);

        // التأكد من أن المخزون نقص في قاعدة البيانات من 5 إلى 3
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 3
        ]);
    }

    public function test_it_throws_an_exception_if_product_is_out_of_stock()
    {
        // 1. تجهيز منتج بمخزون غير كافٍ
        $product = Product::create([
            'name' => 'Out of Stock Product',
            'sku' => 'TEST-002',
            'price' => 50.00,
            'stock' => 1,
            'version' => 1
        ]);

        $data = [
            'user_id' => 1,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 5] // طلب 5 قطع والمخزون قطعة واحدة فقط!
            ]
        ];

        $action = app(PlaceOrderAction::class);

        // 2 & 3. نتوقع أن يرمي الكود Exception يمنع إكمال الطلب
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Product TEST-002 is out of stock.");

        $action->execute($data);
    }

    
}
