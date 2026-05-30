<?php

namespace App\Domains\Order\Observers;

use App\Domains\Order\Models\Order;
use App\Domains\AuditLog\Models\AuditLog;

class OrderObserver
{
    public function created(Order $order): void
    {
        AuditLog::create([
            'auditable_type' => Order::class,
            'auditable_id' => $order->id,
            'event' => 'created',
            'old_values' => null,
            'new_values' => $order->toArray(),
            'user_id' => auth()->id() // سيلتقط معرّف المستخدم تلقائياً لو كان مسجلاً
        ]);
    }

  
    public function updated(Order $order): void
    {
        // جلب البيانات التي تغيرت فقط واستبعاد الـ timestamp لتوفير المساحة
        $dirty = $order->getDirty();
        $old = array_intersect_key($order->getOriginal(), $dirty);

        AuditLog::create([
            'auditable_type' => Order::class,
            'auditable_id' => $order->id,
            'event' => 'updated',
            'old_values' => $old,
            'new_values' => $dirty,
            'user_id' => auth()->id()
        ]);
    }
}