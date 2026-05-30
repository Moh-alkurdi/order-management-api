<?php

namespace Tests\Feature\Domains\AuditLog;

use App\Domains\Order\Models\Order;
use App\Models\User;
use App\Domains\AuditLog\Models\AuditLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_an_order_automatically_logs_audit_trail()
    {
        $user = User::factory()->create();
        // 1. إنشاء طلب (هذا سيحفز الـ Observer تلقائياً)
        $order = Order::create([
            'user_id' => $user->id,
            'customer_id' => 1,
            'order_number' => 'ORD-AUDIT-02',
            'total_amount' => 100.00,
            'status' => 'pending'
        ]);

        // 2. الفحص: هل تم إنشاء سجل في جدول الـ audit_logs؟
        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => Order::class,
            'auditable_id' => $order->id,
            'event' => 'created'
        ]);
    }
}