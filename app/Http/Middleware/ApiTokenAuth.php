<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;
use Laravel\Sanctum\PersonalAccessToken;

class ApiTokenAuth
{
    public function handle(Request $request, Closure $next)
    {
        if ($bearer = $request->bearerToken()) {
            $token = PersonalAccessToken::findToken($bearer);
            if ($token && $token->tokenable) {
                auth()->login($token->tokenable);
                return $next($request);
            }

            $staticToken = config('app.omni_api_token');
            if ($staticToken && hash_equals($staticToken, $bearer)) {
                $user = User::firstOrCreate(
                    ['email' => 'admin@omviportal.com'],
                    [
                        'name' => 'Admin',
                        'password' => bcrypt(\Illuminate\Support\Str::random(32)),
                    ]
                );
                auth()->login($user);
                return $next($request);
            }
        }

        return response()->json(['message' => 'Unauthenticated.'], 401);
    }
}
