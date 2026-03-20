<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * LOGIN
     */
    public function login(Request $request)
    {
        // ✅ VALIDASI INPUT
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // ✅ CEK USER BERDASARKAN EMAIL
        $user = User::where('email', $request->email)->first();

        // ❌ 1. EMAIL TIDAK TERDAFTAR
        if (!$user) {
            return response()->json([
                'message' => 'Email atau user belum terdaftar.'
            ], 404);
        }

        // ❌ 2. PASSWORD SALAH
        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Password yang kamu masukkan salah.'
            ], 401);
        }

        // ✅ 3. LOGIN BERHASIL

        // Hapus token lama (optional tapi bagus biar bersih)
        $user->tokens()->delete();

        // Buat token baru (Sanctum)
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Selamat datang kembali!',
            'token'   => $token,
            'user'    => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->role,
            ]
        ], 200);
    }

    /**
     * LOGOUT
     */
    public function logout(Request $request)
    {
        // Hapus token yang sedang digunakan
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Berhasil logout. Sampai jumpa lagi!'
        ], 200);
    }

    /**
     * GET USER (CEK LOGIN)
     */
    public function me(Request $request)
    {
        return response()->json([
            'user' => $request->user()
        ], 200);
    }
}
