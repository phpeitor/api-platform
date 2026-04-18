<?php

use App\Http\Controllers\ApiTokenController;
use Illuminate\Support\Facades\Route;

// Endpoint de salud sin autenticación
Route::get('/health', function () {
    return response()->json([
        'ok' => true,
        'service' => 'api-platform-laravel',
        'authentication' => 'required for other endpoints',
    ]);
});

// Generador controlado de tokens de API
Route::post('/tokens/generate', [ApiTokenController::class, 'generate']);
