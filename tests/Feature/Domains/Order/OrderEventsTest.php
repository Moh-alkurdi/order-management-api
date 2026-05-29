<?php

namespace Tests\Feature\Domains\Order;

use App\Domains\Order\Actions\PlaceOrderAction;
use App\Domains\Order\Events\OrderPlaced;
use App\Domains\Notification\Listeners\SendOrderNotifications;
use App\Domains\Inventory\Models\Product; // تأكد من الـ Namespace الصحيح للمنتجات لديك
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class OrderEventsTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_placed_event_is_dispatched_when_action_runs_successfully()
    {
        // 1. تجميد الأحداث لمراقبتها
        Event::fake();

        // 2. إنشاء منتج وهمي في قاعدة البيانات وتعيين مخزون له لنجاح العملية
        $product = Product::create([
            'name' => 'Test Laptop',
            'sku' => 'LAP-123',
            'price' => 1000.00,
            'stock' => 10
        ]);

        // 3. تجهيز بيانات الطلب للـ Action (سلة المشتريات)
        $items = [
            [
                'product_id' => $product->id,
                'quantity' => 2,
                'price' => $product->price
            ]
        ];

        // 4. استدعاء الـ Action الحقيقي للمشروع (الذي أصلحناه في اليوم الثالث)
        // ملاحظة: احقن الـ Action يدوياً أو عبر الـ app() للاستفادة من الـ Dependency Injection
        $action = app(PlaceOrderAction::class);
        $action->execute(['user_id' => 1, 'items'=>  $items]);

        // 5. الفحص الهندسي الصارم:
        // هل قام الـ Action بإطلاق حدث OrderPlaced تلقائياً؟
        Event::assertDispatched(OrderPlaced::class);

        // هل نظام Laravel يعرف أن هذا الحدث موجه للـ Listener في دومين الإشعارات؟
        Event::assertListening(
            OrderPlaced::class,
            SendOrderNotifications::class
        );
    }
}