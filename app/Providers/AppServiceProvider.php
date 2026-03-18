<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Paksa HTTPS jika di Railway
        if (config('app.env') === 'production') {
            \URL::forceScheme('https');
        }
    
        // Daftar frontend yang diizinkan akses API
        $allowedOrigins = [
            'https://ecommerce-frontend-86o8.vercel.app',
            'https://oyang12-ecommerce-frontend.vercel.app',
            // tambahkan domain lain jika perlu
        ];
    
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        if (in_array($origin, $allowedOrigins)) {
            header("Access-Control-Allow-Origin: $origin");
            header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        }
    }
}
