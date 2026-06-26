<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            ['category' => 'Minuman', 'name' => 'Air Mineral 600ml', 'barcode' => '8991002101011', 'stock' => 120, 'cost_price' => 2500, 'sell_price' => 4000],
            ['category' => 'Minuman', 'name' => 'Teh Botol 450ml', 'barcode' => '8991002102022', 'stock' => 80, 'cost_price' => 4500, 'sell_price' => 7000],
            ['category' => 'Snack', 'name' => 'Keripik Kentang 60g', 'barcode' => '8991002103033', 'stock' => 60, 'cost_price' => 8000, 'sell_price' => 12000],
            ['category' => 'Makanan', 'name' => 'Indomie Goreng', 'barcode' => '8991002104044', 'stock' => 200, 'cost_price' => 2800, 'sell_price' => 4500],
            ['category' => 'Sembako', 'name' => 'Beras Premium 1kg', 'barcode' => '8991002105055', 'stock' => 40, 'cost_price' => 14000, 'sell_price' => 18000],
            ['category' => 'Perawatan', 'name' => 'Sabun Mandi 250ml', 'barcode' => '8991002106066', 'stock' => 35, 'cost_price' => 12000, 'sell_price' => 17000],
        ];

        foreach ($products as $item) {
            $category = Category::query()->where('name', $item['category'])->first();

            Product::query()->updateOrCreate(
                ['barcode' => $item['barcode']],
                [
                    'category_id' => $category?->id,
                    'name' => $item['name'],
                    'stock' => $item['stock'],
                    'cost_price' => $item['cost_price'],
                    'sell_price' => $item['sell_price'],
                    'is_active' => true,
                ],
            );
        }
    }
}
