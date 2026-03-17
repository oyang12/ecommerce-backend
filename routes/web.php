<?php

Route::get('/debug-db', function () {
    try {
        DB::connection()->getPdo();
        return "DB CONNECTED";
    } catch (\Exception $e) {
        return $e->getMessage();
    }
});
