<?php

namespace App\Domains\Inventory\Actions;

use App\Domains\Inventory\Models\Product;
use Exception;

class ReserveStockAction
{
    /**
     * @param int $productId
     * @param int $quantity
     * @throws Exception
     */
    public function execute(int $productId, int $quantity): Product
    {
        // استخدام lockForUpdate لقفل هذا السجل في قاعدة البيانات حتى تنتهي العملية المادية
        // هذا يمنع أي طلب آخر من التعديل على المخزون في نفس الملي ثانية
        $product = Product::lockForUpdate()->findOrFail($productId);

        if ($product->stock < $quantity) {
            throw new Exception("Product {$product->sku} is out of stock.");
        }

        // خصم الكمية وحفظ التحديث
        $product->stock -= $quantity;
        $product->save();

        return $product;
    }
}