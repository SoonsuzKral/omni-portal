<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

class AdminIpWhitelist
{
    public function handle(Request $request, Closure $next)
    {
        if (!$this->isAdminRoute($request)) {
            return $next($request);
        }

        $whitelist = $this->getWhitelist();

        if (empty($whitelist)) {
            return $next($request);
        }

        $clientIp = $request->ip();

        if ($this->isIpWhitelisted($clientIp, $whitelist)) {
            return $next($request);
        }

        Log::warning('Unauthorized admin access attempt', [
            'ip' => $clientIp,
            'url' => $request->url(),
            'user_agent' => $request->userAgent(),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'Access denied',
                'message' => 'Your IP address is not authorized to access this resource',
            ], 403);
        }

        abort(403, 'Unauthorized IP address');
    }

    protected function isAdminRoute(Request $request): bool
    {
        $path = $request->getPathInfo();
        
        return str_starts_with($path, '/admin') || 
               str_starts_with($path, '/filament');
    }

    protected function getWhitelist(): array
    {
        $config = config('security.admin_whitelist', []);
        
        if (is_string($config)) {
            return array_filter(array_map('trim', explode(',', $config)));
        }
        
        return $config ?? [];
    }

    protected function isIpWhitelisted(string $ip, array $whitelist): bool
    {
        foreach ($whitelist as $allowedIp) {
            if ($this->ipInRange($ip, $allowedIp)) {
                return true;
            }
        }

        return false;
    }

    protected function ipInRange(string $ip, string $range): bool
    {
        if (str_contains($range, '/')) {
            return $this->cidrMatch($ip, $range);
        }

        if (str_contains($range, '-')) {
            return $this->rangeMatch($ip, $range);
        }

        return $ip === $range;
    }

    protected function cidrMatch(string $ip, string $cidr): bool
    {
        [$subnet, $bits] = explode('/', $cidr);
        
        $ip = ip2long($ip);
        $subnet = ip2long($subnet);
        
        $mask = -1 << (32 - (int)$bits);
        
        return ($ip & $mask) === ($subnet & $mask);
    }

    protected function rangeMatch(string $ip, string $range): bool
    {
        [$start, $end] = array_map('trim', explode('-', $range));
        
        $start = trim($start);
        $end = trim($end);
        
        $ip = ip2long($ip);
        $startIp = ip2long($start);
        $endIp = ip2long($end);

        return $ip >= $startIp && $ip <= $endIp;
    }
}