<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Matikan pengecekan relasi agar tidak error saat hapus data
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // 2. Bersihkan tabel
        DB::table('users')->truncate();
        DB::table('products')->truncate();

        // 3. Isi User (Admin & Customer)
        DB::table('users')->insert([
            [
                'name' => 'Admin MyStore',
                'email' => 'admin@mystore.com',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Budi Pembeli',
                'email' => 'budi@gmail.com',
                'password' => Hash::make('password123'),
                'role' => 'customer',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        // 4. Isi 5 Produk (Hanya kolom yang pasti ada di screenshot kamu)
        $products = [
            'Kemeja Aesthetic White', 
            'Celana Chino Modern', 
            'Kaos Polos Minimalist', 
            'Jaket Denim Vintage', 
            'Sepatu Sneakers Urban'
        ];

        foreach ($products as $index => $name) {
            DB::table('products')->insert([
                'name' => $name,
                'slug' => Str::slug($name),
                'description' => 'Produk aesthetic untuk koleksi kamu.',
                'price' => 100000 + ($index * 50000),
                'stock' => 10,
                'image' => 'https://placehold.co/600x400',
                'thumbnail' => 'https://placehold.co/200x200',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 5. Hidupkan kembali pengecekan relasi
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
