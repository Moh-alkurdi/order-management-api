<?php

namespace Tests\Feature\Domains\Order;

use App\Domains\Order\Models\Order;
use App\Domains\Payment\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class OrderPerformanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_endpoint_prevents_n_plus_one_query_problem()
    {
        $user = \App\Models\User::factory()->create();
        
        // Create 5 orders with invoices using database factory concepts
        for ($i = 0; $i < 5; $i++) {
            $order = Order::create([
                'user_id' => $user->id,
                'customer_id' => 1,
                'order_number' => "PERF-ORD-{$i}",
                'total_amount' => 100.00,
                'status' => 'pending'
            ]);

            Invoice::create([
                'order_id' => $order->id,
                'invoice_number' => "INV-PERF-{$i}",
                'amount' => 100.00,
                'status' => 'paid',
                'gateway' => 'stripe'
            ]);
        }

        // Enable DB Query Logging to count the queries
        DB::enableQueryLog();

        // Simulate the API request to fetch all orders
        $response = $this->getJson('/api/v1/orders');
        $response->assertStatus(200);

        // Get total count of executed SQL queries
        $queryCount = count(DB::getQueryLog());

        // A perfect Eager-Loaded architecture should only take 2 queries here:
        // 1. Select * from orders
        // 2. Select * from invoices where order_id in (1,2,3,4,5)
        $this->assertLessThanOrEqual(3, $queryCount, "N+1 Query detected! Too many database hits.");
    }
}