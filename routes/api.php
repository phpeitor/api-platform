<?php

use Illuminate\Support\Facades\Route;

// Endpoint de salud sin autenticación
Route::get('/health', function () {
    return response()->json([
        'ok' => true,
        'service' => 'api-platform-laravel',
        'authentication' => 'required for other endpoints',
    ]);
});
