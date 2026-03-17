<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'status' => 'API RUNNING',
        'app' => 'Ecommerce Backend'
    ]);
});
