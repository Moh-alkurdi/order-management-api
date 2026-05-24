<?php

namespace App\Domains\Order\Models;

use Illuminate\Database\Eloquent\Model;
use App\Domains\Order\Models\OrderItem;

class Order extends Model
{
    protected $fillable = ['user_id', 'order_number', 'total_amount', 'status'];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}