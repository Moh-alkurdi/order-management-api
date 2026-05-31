<?php

namespace App\Domains\Payment\Gateways;

use App\Domains\Payment\Contracts\PaymentGatewayInterface;
use App\Domains\Order\Models\Order;
use Illuminate\Support\Facades\Log;

class PaypalPaymentGateway implements PaymentGatewayInterface
{
    public function charge(Order $order, float $amount): array
    {
        // محاكاة الاتصال بسيرفرات PayPal الحقيقية
        Log::info("🅿 [PayPal] Charging amount: {$amount} € for Order: {$order->order_number}");

        return [
            'success' => true,
            'transaction_id' => 'tx_paypal_' . uniqid(),
            'gateway' => 'paypal'
        ];
    }
}