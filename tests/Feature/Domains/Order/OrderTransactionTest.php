<?php

namespace Tests\Feature\Domains\Order;

use App\Domains\Order\Actions\PlaceOrderAction;
use App\Domains\Inventory\Actions\ReserveStockAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Exception;

class OrderTransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_creation_rolls_back_completely_on_database_failure()
    {
        // 1. نقوم بعمل Mock (محاكاة ساخرة) لـ ReserveStockAction لنجعله يرمي خطأ متعمد
        $mockReserveStock = $this->createMock(ReserveStockAction::class);
        $mockReserveStock->method('execute')->willThrowException(new Exception("Inventory System Crash - Out of Stock"));

        // 2. حقن الـ Mock داخل الـ PlaceOrderAction الحقيقي الخاص بك
        $action = new PlaceOrderAction($mockReserveStock);

        // 3. محاولة تنفيذ الطلب (سيفشل فوراً عند أول منتج بسبب الـ Mock)
        try {
            $action->execute([
                'user_id' => 1,
                'items' => [
                    ['product_id' => 99, 'quantity' => 2]
                ]
            ]);
        } catch (\Throwable $e) {
            // نلتقط الخطأ لمنع التست من الانفجار
        }

        // 4. الفحص الصارم: بفضل الـ DB::transaction الموجودة في كودك، 
        // يجب أن نجد أن جدول الـ orders فارغ تماماً ولم يتم حفظ أي بيانات مشوهة!
        $this->assertDatabaseCount('orders', 0);
    }
}