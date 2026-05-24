<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domains\Inventory\Models\Product;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // إنشاء منتج تجريبي بمخزون 5 قطع فقط لنختبر نفاد المخزون
        $products = [
            [
                'id' => 1,
                'name' => 'PlayStation 5',
                'sku' => 'SONY-PS5-001',
                'price' => 499.99,
                'stock' => 5,
                'version' => 1,
            ],
            [
                'id' => 2,
                'name' => 'Xbox Series X',
                'sku' => 'MS-XBOX-002',
                'price' => 449.99,
                'stock' => 10,
                'version' => 1,
            ],
            [
                'id' => 3,
                'name' => 'Nintendo Switch',
                'sku' => 'NINT-SW-003',
                'price' => 299.99,
                'stock' => 0, // منتج مخزونه صفر لنختبر به حالة الفشل
                'version' => 1,
            ],
        ];

        foreach($products ?? [] as $product){
            Product::updateOrCreate(['id' => $product['id']], $product);
        }
    }
}