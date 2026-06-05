<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Cache;

class EnhancedRateLimiter
{
    protected int $maxAttempts = 60;
    protected int $decaySeconds = 60;

    public function handle(Request $request, Closure $next)
    {
        $key = $this->resolveRequestSignature($request);

        if ($this->isBlocked($key)) {
            Log::warning('IP blocked due to too many attempts', [
                'ip' => $request->ip(),
                'key' => $key,
            ]);

            return response()->json([
                'error' => 'Too many requests',
                'message' => 'You have been blocked temporarily. Please try again later.',
                'retry_after' => Cache::get("rate_limit_block:{$key}", 60),
            ], 429);
        }

        if (RateLimiter::tooManyAttempts($key, $this->maxAttempts)) {
            $this->blockIp($key, 300);
            
            Log::warning('Rate limit exceeded - IP blocked', [
                'ip' => $request->ip(),
                'key' => $key,
            ]);

            return response()->json([
                'error' => 'Rate limit exceeded',
                'message' => 'Too many requests. You have been temporarily blocked.',
            ], 429);
        }

        RateLimiter::hit($key, $this->decaySeconds);

        $response = $next($request);

        $remaining = RateLimiter::remaining($key, $this->maxAttempts);
        
        return $response->withHeaders([
            'X-RateLimit-Limit' => $this->maxAttempts,
            'X-RateLimit-Remaining' => max(0, $remaining),
            'X-RateLimit-Reset' => now()->addSeconds($this->decaySeconds)->timestamp,
        ]);
    }

    protected function resolveRequestSignature(Request $request): string
    {
        $ip = $request->ip();
        
        $route = $request->route()?->getName() ?? $request->path();
        
        return sha1($ip . '|' . $route);
    }

    protected function isBlocked(string $key): bool
    {
        return Cache::has("rate_limit_block:{$key}");
    }

    protected function blockIp(string $key, int $seconds): void
    {
        Cache::put("rate_limit_block:{$key}", true, $seconds);
    }
}