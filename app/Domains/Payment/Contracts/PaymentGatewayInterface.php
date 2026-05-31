<?php

namespace App\Domains\Payment\Contracts;

use App\Domains\Order\Models\Order;

interface PaymentGatewayInterface
{
    // Interface for all payment 
    public function charge(Order $order, float $amount): array;
}