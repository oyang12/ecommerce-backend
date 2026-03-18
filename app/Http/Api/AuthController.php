<?php

namespace App\Http\Api;

// Memastikan alamat induk benar agar tidak Error 500
use App\Http\Controller; 
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Fungsi Login untuk Admin & Pengguna
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        // Cek apakah user ada dan password benar
        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Email atau password yang kamu masukkan salah.'
            ], 401);
        }

        // Hapus token lama agar database bersih, lalu buat token baru (Sanctum)
        $user->tokens()->delete();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Selamat datang kembali!',
            'token'   => $token,
            'user'    => [
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->role, // Penting untuk logika redirect di Next.js
            ]
        ]);
    }

    /**
     * Fungsi Logout
     */
    public function logout(Request $request)
    {
        // Menghapus token yang sedang digunakan saat ini
        $request->user()->currentAccessToken()->delete();
        
        return response()->json([
            'message' => 'Berhasil logout. Sampai jumpa lagi!'
        ]);
    }
}
