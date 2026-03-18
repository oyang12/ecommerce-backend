<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            ['name' => 'Kemeja Aesthetic White', 'price' => 150000, 'stock' => 10],
            ['name' => 'Celana Chino Modern', 'price' => 200000, 'stock' => 5],
            ['name' => 'Kaos Polos Minimalist', 'price' => 85000, 'stock' => 20],
            ['name' => 'Jaket Denim Vintage', 'price' => 350000, 'stock' => 3],
            ['name' => 'Sepatu Sneakers Urban', 'price' => 450000, 'stock' => 7],
        ];

        foreach ($products as $product) {
            DB::table('products')->insert([
                'name'        => $product['name'],
                'slug'        => Str::slug($product['name']),
                'description' => 'Produk berkualitas tinggi dengan desain ' . $product['name'],
                'price'       => $product['price'],
                'stock'       => $product['stock'],
                // Sementara kita isi URL dummy dulu agar tidak kosong
                'image'       => 'https://placehold.co/600x400?text=' . urlencode($product['name']),
                'thumbnail'   => 'https://placehold.co/200x200?text=Thumb',
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }
    }
}
