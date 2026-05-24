<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class IdempotencyMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->isMethod('POST')) {
            return $next($request);
        }

        $idempotencyKey = $request->header('X-Idempotency-Key');

        if (!$idempotencyKey) {
            return response()->json([
                'error' => 'Bad Request',
                'message' => 'X-Idempotency-Key header is missing.'
            ], 400);
        }

        $cacheKey = "idempotency:{$idempotencyKey}";

        if (Cache::has($cacheKey)) {
            $cachedResponse = Cache::get($cacheKey);
            
            return response()->json($cachedResponse['body'], $cachedResponse['status'])
                             ->header('X-Cache-Lookup', 'HIT - Duplicate Request Blocked');
        }

        $response = $next($request);

        if ($response->isSuccessful()) {
            Cache::put($cacheKey, [
                'body' => json_decode($response->getContent(), true),
                'status' => $response->getStatusCode()
            ], now()->addHours(24));
        }

        return $response;
    }
}
