<?php

Route::get('/', function () {
    return response()->json([
        'status' => 'API RUNNING',
        'app' => 'Ecommerce Backend'
    ]);
});
