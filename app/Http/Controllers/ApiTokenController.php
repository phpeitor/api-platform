<?php

namespace App\Http\Controllers;

use App\Models\ApiToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ApiTokenController extends Controller
{
    public function generate(Request $request): JsonResponse
    {
        $adminSecret = (string) config('services.api_token_generator.secret', env('API_TOKEN_GENERATOR_SECRET'));

        if ($adminSecret === '') {
            return response()->json([
                'error' => 'Token generator is disabled',
                'message' => 'Set API_TOKEN_GENERATOR_SECRET in .env to enable this endpoint.',
            ], 503);
        }

        $providedSecret = (string) $request->header('X-API-ADMIN-TOKEN', '');

        if (!hash_equals($adminSecret, $providedSecret)) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Invalid admin token.',
            ], 403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'expires_in_days' => ['nullable', 'integer', 'min:1', 'max:3650'],
        ]);

        $plainToken = 'token_' . Str::random(60);
        $expiresInDays = (int) ($validated['expires_in_days'] ?? config('services.api_token_generator.default_expires_in_days', 30));
        $expiresAt = now()->addDays($expiresInDays);

        $token = ApiToken::create([
            'name' => $validated['name'],
            'token' => $plainToken,
            'description' => $validated['description'] ?? null,
            'expires_at' => $expiresAt,
        ]);

        return response()->json([
            'ok' => true,
            'data' => [
                'id' => $token->id,
                'name' => $token->name,
                'description' => $token->description,
                'token' => $plainToken,
                'expires_at' => optional($token->expires_at)->toISOString(),
                'created_at' => $token->created_at?->toISOString(),
            ],
            'message' => 'Token creado exitosamente. Guarda este valor, no volverá a mostrarse.',
        ], 201);
    }
}
