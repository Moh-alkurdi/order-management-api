<?php

namespace Tests\Feature\Domains\Order;

use App\Domains\Order\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class OrderApiCacheTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_show_endpoint_caches_order_in_redis()
    {
        $user = \App\Models\User::factory()->create();
        $order = Order::create([
            'user_id' => $user->id,
            'customer_id' => 1,
            'order_number' => 'ORD-CACHE-77',
            'total_amount' => 120.00,
            'status' => 'pending'
        ]);

        // First Request: Triggers DB query and caches the result
        $response = $this->getJson("/api/v1/orders/{$order->id}");
        $response->assertStatus(200);

        // Verification: Check if the key exists in Redis/Cache Store
        $this->assertTrue(Cache::has("order_show_{$order->id}"));
    }
}