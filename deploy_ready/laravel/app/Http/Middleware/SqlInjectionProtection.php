<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SqlInjectionProtection
{
    protected array $patterns = [
        '/(\s)+(select|insert|update|delete|drop|create|alter|truncate|union|into|load_file|outfile|dump).*(\s)+(from|into|table|database)/i',
        '/(union|select|insert|update|delete|drop|create|alter|truncate).*?(from|into|table|database)/i',
        '/--|\/\*|\*\/|@@|char\(|nchar\(|varchar\(|nvarchar\(|\b(?:alter|begin|cast|create|cursor|declare|delete|drop|end|exec|execute|fetch|insert|kill|open|select|sys(?:objects|columns)?|table|update)\b|xp_/i',
        '/exec\s*\(.*?\)/i',
        '/execute\s*\(.*?\)/i',
        '/into\s+(outfile|dumpfile)/i',
        '/union\s+(all\s+)?select/i',
        '/sleep\s*\(/i',
        '/waitfor\s+delay/i',
        '/benchmark\s*\(/i',
        '/load_file\s*\(/i',
        '/into\s+dumpfile/i',
    ];

    protected array $exceptRoutes = [
        'api/',
        'sitemap',
        'search',
        'keywords/',
        'admin/',
        'filament/',
    ];

    public function handle(Request $request, Closure $next)
    {
        if ($this->shouldSkip($request)) {
            return $next($request);
        }

        $inputs = $request->all();

        foreach ($inputs as $key => $value) {
            if (is_string($value) && $this->containsSqlInjection($value)) {
                Log::warning('SQL Injection attempt detected', [
                    'ip' => $request->ip(),
                    'url' => $request->url(),
                    'input_key' => $key,
                    'input_value' => $value,
                    'user_agent' => $request->userAgent(),
                ]);

                if ($request->expectsJson()) {
                    return response()->json([
                        'error' => 'Invalid input detected',
                        'message' => 'Request blocked for security reasons',
                    ], 403);
                }

                abort(403, 'Invalid input detected');
            }
        }

        $uri = $request->getRequestUri();
        if ($this->containsSqlInjection($uri)) {
            Log::warning('SQL Injection attempt in URI', [
                'ip' => $request->ip(),
                'uri' => $uri,
            ]);

            abort(403, 'Invalid request');
        }

        return $next($request);
    }

    protected function shouldSkip(Request $request): bool
    {
        $path = $request->getPathInfo();
        
        foreach ($this->exceptRoutes as $route) {
            if (str_starts_with($path, '/' . $route)) {
                return true;
            }
        }

        return false;
    }

    protected function containsSqlInjection(string $value): bool
    {
        foreach ($this->patterns as $pattern) {
            if (preg_match($pattern, $value)) {
                return true;
            }
        }

        return false;
    }
}