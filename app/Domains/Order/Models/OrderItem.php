<?php

namespace App\Domains\Order\Models;

use Illuminate\Database\Eloquent\Model;
use App\Domains\Inventory\Models\Product;

class OrderItem extends Model
{
    protected $fillable = ['order_id', 'product_id', 'quantity', 'price'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}