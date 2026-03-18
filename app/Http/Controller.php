<?php

namespace App\Http\Api;

// Memastikan AuthController mengenali induknya di App\Http\Controllers\Controller
use App\Http\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // 1. Validasi input
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // 2. Cari user berdasarkan email
        $user = User::where('email', $request->email)->first();

        // 3. Cek apakah user ada dan password-nya cocok (Hash::check)
        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Email atau password salah.'
            ], 401);
        }

        // 4. Hapus token lama agar database clean (Opsional)
        $user->tokens()->delete();
        
        // 5. Buat token baru menggunakan Laravel Sanctum
        $token = $user->createToken('auth_token')->plainTextToken;

        // 6. Kirim respon sukses beserta data user dan token
        return response()->json([
            'message' => 'Login Berhasil',
            'token'   => $token,
            'user'    => [
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->role, // Penting untuk membedakan Admin dan Customer di Next.js
            ]
        ]);
    }

    public function logout(Request $request)
    {
        // Menghapus token yang sedang aktif saat ini
        $request->user()->currentAccessToken()->delete();
        
        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }
}
