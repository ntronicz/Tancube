<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if($this->app->environment('production')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        \Illuminate\Support\Facades\View::composer(['components.modals.add-lead', 'components.modals.add-task'], function ($view) {
            $user = \Illuminate\Support\Facades\Auth::user();
            if ($user) {
                $organizationId = $user->organization_id;
                $isSuperAdmin = $user->role === 'SUPER_ADMIN';

                $sources = \App\Models\AppSetting::getSources($organizationId);
                $courses = \App\Models\AppSetting::getCourses($organizationId);

                if ($isSuperAdmin) {
                    $agents = \App\Models\User::whereIn('role', ['ADMIN', 'AGENT'])->get(['id', 'name']);
                } else {
                    $agents = \App\Models\User::where('organization_id', $organizationId)
                        ->whereIn('role', ['ADMIN', 'AGENT'])
                        ->get(['id', 'name']);
                }
                
                $view->with(compact('sources', 'courses', 'agents'));
            }
        });
    }
}
