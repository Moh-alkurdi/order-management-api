<?php

namespace App\Domains\Payment\Models;

use App\Domains\Order\Models\Order;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = ['order_id', 'invoice_number', 'amount', 'status', 'transaction_id', 'gateway'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}