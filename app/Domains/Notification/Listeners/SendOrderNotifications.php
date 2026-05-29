<?php

namespace App\Domains\Notification\Listeners;

use App\Domains\Order\Events\OrderPlaced;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class SendOrderNotifications implements ShouldQueue
{
    public function handle(OrderPlaced $event): void
    {
        $order = $event->order;

        Log::info("🔔 [Notification Domain] sending Email & SMS to customer for Order: {$order->order_number}");
    }
}