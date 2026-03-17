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
    
        // Izinkan Vercel mengakses API Laravel
        header('Access-Control-Allow-Origin: https://ecommerce-frontend-86o8.vercel.app/');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
    }
}
