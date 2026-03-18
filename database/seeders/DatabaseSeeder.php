<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. SEEDER USER (Admin & Customer)
        // Hapus user lama agar tidak duplikat saat seeding ulang
        DB::table('users')->truncate();

        User::create([
            'name'     => 'Admin MyStore',
            'email'    => 'admin@mystore.com',
            'password' => Hash::make('password123'),
            'role'     => 'admin',
        ]);

        User::create([
            'name'     => 'Budi Pembeli',
            'email'    => 'budi@gmail.com',
            'password' => Hash::make('password123'),
            'role'     => 'customer',
        ]);

        // 2. SEEDER PRODUCT (5 Produk)
        DB::table('products')->truncate();

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
                'image'       => 'https://placehold.co/600x400?text=' . urlencode($product['name']),
                'thumbnail'   => 'https://placehold.co/200x200?text=Thumb',
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }
    }
}
