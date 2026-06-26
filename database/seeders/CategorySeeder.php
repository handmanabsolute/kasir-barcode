<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Makanan',
            'Minuman',
            'Snack',
            'Sembako',
            'Perawatan',
        ];

        foreach ($categories as $name) {
            Category::query()->updateOrCreate(['name' => $name]);
        }
    }
}
