<?php

namespace App\Domains\Payment\Gateways;

use App\Domains\Payment\Contracts\PaymentGatewayInterface;
use App\Domains\Order\Models\Order;
use Illuminate\Support\Facades\Log;

class KlarnaPaymentGateway implements PaymentGatewayInterface
{
    public function charge(Order $order, float $amount): array
    {
        Log::info("🅿 [Klarna] Charging amount: {$amount} € for Order: {$order->order_number}");

        return [
            'success' => true,
            'transaction_id' => 'ex_klarna_' . uniqid(),
            'gateway' => 'klarna'
        ];
    }
}