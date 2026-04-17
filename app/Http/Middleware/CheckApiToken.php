<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class CheckApiToken
{
    public function handle(Request $request, Closure $next): Response
    {
        // Permitir acceso a /health sin autenticación
        if ($request->path() === 'api/health') {
            return $next($request);
        }

        // Obtener el token del header Authorization
        $authHeader = $request->header('Authorization');
        
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return response()->json([
                'error' => 'Missing or invalid Authorization header',
                'message' => 'Please provide a valid Bearer token in the Authorization header',
                'example' => 'Authorization: Bearer YOUR_TOKEN_HERE',
            ], 401);
        }

        $token = substr($authHeader, 7); // Remover "Bearer "

        // Validar que el token exista en la base de datos
        $apiToken = DB::table('api_tokens')->where('token', $token)->first();

        if (!$apiToken) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Invalid or expired token',
            ], 403);
        }

        // Actualizar last_used_at
        DB::table('api_tokens')
            ->where('token', $token)
            ->update(['last_used_at' => now()]);

        // Pasar el token a travès del request para logs si es necesario
        $request->merge(['api_token_id' => $apiToken->id, 'api_token_name' => $apiToken->name]);

        return $next($request);
    }
}
