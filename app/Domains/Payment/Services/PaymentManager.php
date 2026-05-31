<?php

namespace App\Domains\Payment\Services;

use App\Domains\Payment\Contracts\PaymentGatewayInterface;
use App\Domains\Payment\Gateways\StripePaymentGateway;
use App\Domains\Payment\Gateways\PaypalPaymentGateway;
use App\Domains\Payment\Gateways\KlarnaPaymentGateway;


use InvalidArgumentException;

class PaymentManager
{
    /**
     * دالة ديناميكية تعيد الـ Strategy المناسبة
     */
    public function make(string $gatewayName): PaymentGatewayInterface
    {
        return match (strtolower($gatewayName)) {
            'stripe' => new StripePaymentGateway(),
            'paypal' => new PaypalPaymentGateway(),
            'klarna' => new KlarnaPaymentGateway(),
            default  => throw new InvalidArgumentException("Payment gateway [{$gatewayName}] is not supported.")
        };
    }
}