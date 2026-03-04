<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use App\Models\AppSetting;

class SetUserTimezone
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            $organizationId = $user->organization_id;
            
            // Fetch timezone from general settings
            $generalSettings = AppSetting::getForOrganization($organizationId, 'general');
            $timezone = $generalSettings['timezone'] ?? 'Asia/Kolkata'; // Default to Indian Time
            
            // Set application timezone
            Config::set('app.timezone', $timezone);
            date_default_timezone_set($timezone);
        }

        return $next($request);
    }
}
