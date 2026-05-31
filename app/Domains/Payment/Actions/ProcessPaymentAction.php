<?php

namespace App\Domains\Payment\Actions;

use App\Domains\Order\Models\Order;
use App\Domains\Payment\Services\PaymentManager;
use App\Domains\Payment\Models\Invoice;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Exception;

class ProcessPaymentAction
{
    protected $paymentManager;

    public function __construct(PaymentManager $paymentManager)
    {
        $this->paymentManager = $paymentManager;
    }

    public function execute(Order $order, string $gatewayName, string $idempotencyKey): Invoice
    {
        // 1. Echter Redis-Atomic-Lock für 10 Sekunden setzen
        // Wenn der gleiche Key innerhalb von 10 Sek. kommt, blockiert Redis den Request.
        $lock = Cache::lock('payment_lock_' . $idempotencyKey, 10);

        if (!$lock->get()) {
            throw new Exception("Duplicate request detected. Processing is already underway.");
        }

        try {
            // 2. Prüfen, ob die Rechnung bereits in der DB existiert (Idempotency Check)
            $existingInvoice = Invoice::where('order_id', $order->id)->first();
            if ($existingInvoice) {
                $lock->release();
                return $existingInvoice;
            }

            // 3. Strategie auswählen (Stripe / PayPal) über unseren PaymentManager
            $gateway = $this->paymentManager->make($gatewayName);
            
            // 4. Zahlung ausführen
            $paymentResult = $gateway->charge($order, $order->total_amount);

            if (!$paymentResult['success']) {
                throw new Exception("Payment failed via gateway.");
            }

            // 5. Echte Rechnung in der Datenbank erzeugen innerhalb einer Transaction
            return DB::transaction(function () use ($order, $paymentResult, $gatewayName) {
                return Invoice::create([
                    'order_id'       => $order->id,
                    'invoice_number' => 'INV-' . strtoupper(uniqid()),
                    'amount'         => $order->total_amount,
                    'status'         => 'paid',
                    'transaction_id' => $paymentResult['transaction_id'],
                    'gateway'        => $gatewayName,
                ]);
            });

        } finally {
            // 6. Schloss in Redis nach Erfolg oder Fehler immer wieder freigeben
            $lock->release();
        }
    }
}