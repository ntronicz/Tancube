<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class SimpleApiTokenMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Check for Simple API Token (header only — never accept in query string)
        $token = $request->header('X-API-TOKEN');

        if ($token) {
            $user = User::where('api_token', $token)->first();
            if ($user) {
                Auth::login($user);
                return $next($request);
            }
        }

        // 2. Fallback to JWT (auth:api)
        // We use the 'api' guard to check if a Bearer token is valid
        if (Auth::guard('api')->check()) {
            // Ensure the user is set for the default guard too if needed
            Auth::shouldUse('api');
            return $next($request);
        }

        return response()->json(['message' => 'Unauthenticated.'], 401);
    }
}
