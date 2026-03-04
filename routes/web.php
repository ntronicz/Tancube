<?php

use App\Http\Controllers\Web\LoginController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\LeadController;
use App\Http\Controllers\Web\TaskController;
use App\Http\Controllers\Web\InsightsController;
use App\Http\Controllers\Web\SettingsController;
use App\Http\Controllers\Web\AdminController;
use App\Http\Controllers\Web\CallMetricsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:5,1');
});

// Mobile app WebView token-based login (no auth middleware - it validates JWT internally)
Route::get('/auth/token-login', [LoginController::class, 'tokenLogin'])->name('auth.token-login');

// Authenticated routes
Route::middleware('auth')->group(function () {
    // Logout
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Subscription Expired (Accessible by auth users)
    Route::get('/subscription-expired', function () {
        return view('subscription.expired');
    })->name('subscription.expired');

    // Dashboard
    Route::middleware('subscription')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard', [DashboardController::class, 'index']);
    });

    // Leads
    Route::middleware('subscription')->group(function () {
        Route::get('/leads', [LeadController::class, 'index'])->name('leads.index');
    Route::get('/leads/create', [LeadController::class, 'create'])->name('leads.create');
    Route::get('/leads/export', [LeadController::class, 'export'])->name('leads.export');
    Route::get('/leads/sample', [LeadController::class, 'downloadSample'])->name('leads.sample');
    Route::post('/leads', [LeadController::class, 'store'])->name('leads.store');
    Route::post('/leads/import', [LeadController::class, 'import'])->name('leads.import');
    Route::post('/leads/bulk-assign', [LeadController::class, 'bulkAssign'])->name('leads.bulk-assign');
    Route::post('/leads/bulk-delete', [LeadController::class, 'bulkDelete'])->name('leads.bulk-delete');
    Route::post('/leads/bulk-status-update', [LeadController::class, 'bulkStatusUpdate'])->name('leads.bulk-status-update');
    Route::get('/leads/{id}', [LeadController::class, 'show'])->name('leads.show');
    Route::put('/leads/{id}', [LeadController::class, 'update'])->name('leads.update');
    Route::delete('/leads/{id}', [LeadController::class, 'destroy'])->name('leads.destroy');
    Route::post('/leads/{id}/follow-up', [LeadController::class, 'updateFollowUp'])->name('leads.follow-up');
    Route::post('/leads/{id}/log-activity', [LeadController::class, 'logActivity'])->name('leads.log-activity');

    }); // End subscription middleware group

    // Follow-ups Tab (also needs subscription)
    Route::middleware('subscription')->get('/follow-ups', [LeadController::class, 'followUps'])->name('follow-ups.index');

    // Tasks (needs subscription)
    Route::middleware('subscription')->group(function () {
        Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
        Route::get('/tasks/create', [TaskController::class, 'create'])->name('tasks.create');
        Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
        Route::put('/tasks/{id}', [TaskController::class, 'update'])->name('tasks.update');
        Route::delete('/tasks/{id}', [TaskController::class, 'destroy'])->name('tasks.destroy');
        Route::post('/tasks/{id}/toggle', [TaskController::class, 'toggleStatus'])->name('tasks.toggle');
    });

    // Insights (needs subscription)
    Route::middleware('subscription')->get('/insights', [InsightsController::class, 'index'])->name('insights');

    // Call Metrics (needs subscription)
    Route::middleware('subscription')->get('/call-metrics', [CallMetricsController::class, 'index'])->name('call-metrics');

    // Notifications
    Route::post('/notifications/read', [SettingsController::class, 'markNotificationsRead'])->name('notifications.read');

    // Settings (Admin only)
    Route::middleware('role:ADMIN,SUPER_ADMIN')->prefix('settings')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('settings.index');
        Route::put('/profile', [SettingsController::class, 'updateProfile'])->name('settings.profile.update');
        Route::post('/profile/api-token', [SettingsController::class, 'generateApiToken'])->name('settings.profile.token');
        Route::put('/general', [SettingsController::class, 'updateGeneral'])->name('settings.general.update');
        Route::put('/sources', [SettingsController::class, 'updateSources'])->name('settings.sources.update');
        Route::put('/courses', [SettingsController::class, 'updateCourses'])->name('settings.courses.update');
        Route::put('/statuses', [SettingsController::class, 'updateStatuses'])->name('settings.statuses.update');
        Route::post('/users', [SettingsController::class, 'storeUser'])->name('settings.users.store');
        Route::put('/users/{id}', [SettingsController::class, 'updateUser'])->name('settings.users.update');
        Route::delete('/users/{id}', [SettingsController::class, 'destroyUser'])->name('settings.users.destroy');
        Route::post('/webhooks', [SettingsController::class, 'storeWebhook'])->name('settings.webhooks.store');
        Route::delete('/webhooks/{id}', [SettingsController::class, 'destroyWebhook'])->name('settings.webhooks.destroy');
        Route::delete('/activity-logs', [SettingsController::class, 'clearLogs'])->name('settings.logs.clear');
        Route::get('/backup', [SettingsController::class, 'downloadBackup'])->name('settings.backup');
        Route::put('/backup', [SettingsController::class, 'updateBackupSettings'])->name('settings.backup.update');
        Route::post('/restore', [SettingsController::class, 'restoreBackup'])->name('settings.restore');
    });

    // Super Admin routes
    Route::middleware('role:SUPER_ADMIN')->prefix('admin')->group(function () {
        // Dashboard
        Route::get('/', [AdminController::class, 'index'])->name('admin.dashboard');

        // Vendors
        Route::get('/vendors', [AdminController::class, 'vendors'])->name('admin.vendors');
        Route::post('/vendors', [AdminController::class, 'storeVendor'])->name('admin.vendors.store');
        Route::get('/vendors/{id}', [AdminController::class, 'showVendor'])->name('admin.vendors.show');
        Route::put('/vendors/{id}', [AdminController::class, 'updateVendor'])->name('admin.vendors.update');
        Route::delete('/vendors/{id}', [AdminController::class, 'destroyVendor'])->name('admin.vendors.destroy');
        Route::post('/vendors/{id}/toggle', [AdminController::class, 'toggleVendor'])->name('admin.vendors.toggle');

        // Subscriptions
        Route::get('/subscriptions', [AdminController::class, 'subscriptions'])->name('admin.subscriptions');
        Route::post('/subscriptions', [AdminController::class, 'storeSubscription'])->name('admin.subscriptions.store');
        Route::put('/subscriptions/{id}/limits', [AdminController::class, 'updateSubscriptionLimits'])->name('admin.subscriptions.limits');
        Route::post('/subscriptions/{id}/cancel', [AdminController::class, 'cancelSubscription'])->name('admin.subscriptions.cancel');
        Route::post('/subscriptions/{id}/renew', [AdminController::class, 'renewSubscription'])->name('admin.subscriptions.renew');

        // Plans
        Route::get('/plans', [AdminController::class, 'plans'])->name('admin.plans');
        Route::post('/plans', [AdminController::class, 'storePlan'])->name('admin.plans.store');
        Route::put('/plans/{id}', [AdminController::class, 'updatePlan'])->name('admin.plans.update');
        Route::delete('/plans/{id}', [AdminController::class, 'destroyPlan'])->name('admin.plans.destroy');
        Route::post('/plans/{id}/toggle', [AdminController::class, 'togglePlan'])->name('admin.plans.toggle');
    });
});
