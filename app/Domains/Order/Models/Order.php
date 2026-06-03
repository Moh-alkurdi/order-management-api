<?php

namespace App\Domains\Order\Models;

use App\Domains\Payment\Models\Invoice;
use Illuminate\Database\Eloquent\Model;
use App\Domains\Order\Models\OrderItem;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    protected $fillable = ['user_id', 'order_number', 'total_amount', 'status'];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get the invoice associated with the order (Cross-Domain Relation)
    */
    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

}