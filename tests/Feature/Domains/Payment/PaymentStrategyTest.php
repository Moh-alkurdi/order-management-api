<?php

namespace Tests\Feature\Domains\Payment;

use App\Domains\Order\Models\Order;
use App\Domains\Payment\Models\Invoice;
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

    public function test_payment_avoids_duplicate_processing_using_redis_lock()
    {
        $user = \App\Models\User::factory()->create();
        $order = Order::create([
            'user_id' => $user->id,
            'customer_id' => 1,
            'order_number' => 'ORD-REDIS-99',
            'total_amount' => 500.00,
            'status' => 'pending'
        ]);

        $action = app(\App\Domains\Payment\Actions\ProcessPaymentAction::class);
        $idempotencyKey = 'unique_request_key_12345';

        // Erste Ausführung: Muss erfolgreich sein und eine echte Rechnung erstellen
        $invoice = $action->execute($order, 'stripe', $idempotencyKey);
        $this->assertInstanceOf(Invoice::class, $invoice);
        $this->assertEquals('paid', $invoice->status);

        // Zweite Ausführung mit exakt gleichem Key (Simulierter Doppelklick)
        // Da die Rechnung bereits existiert, gibt der Idempotent Consumer die bestehende Invoice zurück, statt neu zu laden!
        $secondInvoice = $action->execute($order, 'stripe', $idempotencyKey);
        $this->assertEquals($invoice->id, $secondInvoice->id);
        
        // Überprüfung, ob nur EINE Rechnung in der Datenbank existiert
        $this->assertDatabaseCount('invoices', 1);
    }
}