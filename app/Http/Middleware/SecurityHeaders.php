<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');

        // Content Security Policy - AdSense uyumlu
        $isLocal = app()->environment('local');
        $viteScript = $isLocal ? 'http://localhost:5173 http://127.0.0.1:5173' : '';
        $viteConnect = $isLocal ? 'http://localhost:5173 http://127.0.0.1:5173 ws://localhost:5173 ws://127.0.0.1:5173' : '';
        $csp = "default-src 'self'; " .
               "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://pagead2.googlesyndication.com https://googleads.g.doubleclick.net https://www.googletagmanager.com https://cdn.tailwindcss.com https://cdnjs.cloudflare.com https://fonts.googleapis.com https://www.google.com https://www.gstatic.com {$viteScript}; " .
               "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdnjs.cloudflare.com https://pagead2.googlesyndication.com; " .
               "img-src 'self' data: blob: https: http://127.0.0.1:5173 http://localhost:5173; " .
               "frame-src 'self' https://googleads.g.doubleclick.net https://pagead2.googlesyndication.com; " .
               "connect-src 'self' https://pagead2.googlesyndication.com https://partner.googleadservices.com https://googleads.g.doubleclick.net https://adservice.google.com https://*.googlesyndication.com https://google-analytics.com https://www.google-analytics.com https://*.google-analytics.com https://analytics.google.com https://www.googletagmanager.com https://stats.g.doubleclick.net {$viteConnect}; " .
               "font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com; " .
               "object-src 'none'; " .
               "base-uri 'self'; " .
               "form-action 'self';";
        $response->headers->set('Content-Security-Policy', $csp);

        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }

        if (str_starts_with($request->path(), 'admin') || str_starts_with($request->path(), 'api')) {
            $response->headers->set('X-Robots-Tag', 'noindex, nofollow');
        }

        return $response;
    }
}
