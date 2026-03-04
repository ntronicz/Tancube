<?php

namespace App\Http\Middleware;

use App\Models\Lead;
use App\Models\Task;
use App\Models\User;
use App\Models\ActivityLog;
use App\Models\Webhook;
use App\Models\AppSetting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantScope
{
    /**
     * Handle an incoming request.
     * Automatically scope models to the current user's organization.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->organization_id) {
            // Apply global scopes to multi-tenant models
            Lead::addGlobalScope('organization', function ($query) use ($user) {
                $query->where('organization_id', $user->organization_id);
            });

            Task::addGlobalScope('organization', function ($query) use ($user) {
                $query->where('organization_id', $user->organization_id);
            });

            User::addGlobalScope('organization', function ($query) use ($user) {
                $query->where('organization_id', $user->organization_id);
            });

            ActivityLog::addGlobalScope('organization', function ($query) use ($user) {
                $query->where('organization_id', $user->organization_id);
            });

            Webhook::addGlobalScope('organization', function ($query) use ($user) {
                $query->where('organization_id', $user->organization_id);
            });

            AppSetting::addGlobalScope('organization', function ($query) use ($user) {
                $query->where('organization_id', $user->organization_id);
            });
        }

        return $next($request);
    }
}
