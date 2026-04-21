<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class LogEndpointQuery
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->is('api/*') && $request->path() !== 'api') {
            return $next($request);
        }

        $startTime = microtime(true);
        $response = null;
        $exceptionMessage = null;

        try {
            $response = $next($request);

            return $response;
        } catch (Throwable $exception) {
            $exceptionMessage = $exception->getMessage();

            throw $exception;
        } finally {
            $durationMs = round((microtime(true) - $startTime) * 1000, 2);

            Log::channel('endpoint_queries')->info('Endpoint query', [
                'method' => $request->method(),
                'path' => '/'.$request->path(),
                'full_url' => $request->fullUrl(),
                'status_code' => $response?->getStatusCode() ?? 500,
                'duration_ms' => $durationMs,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'query_params' => $this->sanitizeData($request->query()),
                'body' => $this->sanitizeData($request->except(['password', 'password_confirmation'])),
                'api_token_id' => $request->input('api_token_id'),
                'api_token_name' => $request->input('api_token_name'),
                'exception' => $exceptionMessage,
            ]);
        }
    }

    private function sanitizeData(array $data): array
    {
        $sensitiveKeys = [
            'authorization',
            'token',
            'password',
            'password_confirmation',
            'secret',
            'api_key',
        ];

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->sanitizeData($value);
                continue;
            }

            if (in_array(strtolower((string) $key), $sensitiveKeys, true)) {
                $data[$key] = '***';
                continue;
            }

            if (is_string($value) && strlen($value) > 1000) {
                $data[$key] = substr($value, 0, 1000).'...[truncated]';
            }
        }

        return $data;
    }
}