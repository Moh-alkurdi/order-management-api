<?php

namespace Tests\Feature\Domains\Payment;

use App\Domains\Order\Models\Order;
use App\Domains\Payment\Services\PaymentManager;
use App\Domains\Payment\Gateways\StripePaymentGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentStrategyTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_manager_returns_correct_stripe_strategy()
    {
        $manager = new PaymentManager();
        
        // جلب بوابة سترايب عبر المانجر
        $gateway = $manager->make('stripe');

        // التحقق أن الكلاس الناتج ينتمي فعلاً لسترايب
        $this->assertInstanceOf(StripePaymentGateway::class, $gateway);
    }

    public function test_payment_gateway_can_charge_successfully()
    {
        $user = \App\Models\User::factory()->create();
        $order = Order::create([
            'user_id' => $user->id,
            'customer_id' => 1,
            'order_number' => 'ORD-PAY-01',
            'total_amount' => 250.00,
            'status' => 'pending'
        ]);

        $manager = new PaymentManager();
        $result = $manager->make('stripe')->charge($order, 250.00);

        // التثبت من نجاح العملية وعودة الـ Transaction ID
        $this->assertTrue($result['success']);
        $this->assertStringContainsString('ch_stripe_', $result['transaction_id']);
    }
}