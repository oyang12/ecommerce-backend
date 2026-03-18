<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Membuat Akun Admin
        User::create([
            'name'     => 'Admin MyStore',
            'email'    => 'admin@mystore.com',
            'password' => Hash::make('password123'), // Jangan lupa ganti nanti ya!
            'role'     => 'admin',
        ]);

        // 2. Membuat Akun Pengguna Biasa (Customer)
        User::create([
            'name'     => 'Budi Pembeli',
            'email'    => 'budi@gmail.com',
            'password' => Hash::make('password123'),
            'role'     => 'customer',
        ]);
    }
}
