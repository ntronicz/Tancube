<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use Illuminate\Support\Facades\Auth;

class EnsureActiveSubscription
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Super Admins are exempt
        if ($user && $user->role === 'SUPER_ADMIN') {
            return $next($request);
        }

        // Check if user belongs to an organization/vendor
        if ($user && $user->organization) {
            $vendor = $user->organization;
            
            // Check for active subscription or inactive status
            $isInactive = $vendor->status === 'INACTIVE';
            $hasNoSubscription = !$vendor->hasActiveSubscription();

            if ($isInactive || $hasNoSubscription) {
                // Determine if this is an AJAX request or normal page load
                if ($request->expectsJson()) {
                    $message = $isInactive ? 'Account is inactive.' : 'Subscription expired.';
                    return response()->json(['message' => $message], 403);
                }

                // Allow access to the expired page and logout route to prevent redirect loops
                if ($request->routeIs('subscription.expired') || $request->routeIs('logout')) {
                    return $next($request);
                }
                
                // Redirect to expired page
                return redirect()->route('subscription.expired');
            }
            
            // If they have access but are trying to access the expired page, redirect to dashboard
            if ($request->routeIs('subscription.expired')) {
                return redirect()->route('dashboard');
            }
        }

        return $next($request);
    }
}
