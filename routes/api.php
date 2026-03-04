<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\LeadController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\VendorController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\BackupController;
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\Api\WebhookController;
use App\Http\Controllers\Api\ActivityLogController;
use App\Http\Controllers\Api\CallLogController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');

// Protected routes
Route::middleware('auth.simple')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/refresh', [AuthController::class, 'refresh']);

    // Dashboard
    Route::get('/stats', [DashboardController::class, 'stats']);
    Route::get('/insights', [DashboardController::class, 'insights']);

    // Lead phone numbers for call filtering (mobile app) - MUST be before apiResource
    Route::get('/leads/phone-numbers', [LeadController::class, 'phoneNumbers']);

    // Leads
    Route::apiResource('leads', LeadController::class)->names('api.leads');
    Route::post('/leads/import', [LeadController::class, 'import']);
    Route::get('/leads/export/csv', [LeadController::class, 'export']);
    Route::post('/leads/{id}/follow-up', [LeadController::class, 'updateFollowUp']);

    // Tasks
    Route::apiResource('tasks', TaskController::class)->names('api.tasks');
    Route::post('/tasks/{id}/complete', [TaskController::class, 'markComplete']);

    // Call Logs (Mobile App Sync)
    Route::post('/call-logs/sync', [CallLogController::class, 'sync']);
    Route::get('/call-logs', [CallLogController::class, 'index']);
    Route::get('/call-logs/stats', [CallLogController::class, 'stats']);

    // Device Token (FCM)
    Route::post('/device-token', [CallLogController::class, 'registerDeviceToken']);

    // Notifications
    Route::get('/notifications', [CallLogController::class, 'notifications']);
    Route::post('/notifications/read', [CallLogController::class, 'markNotificationsRead']);

    // Users (Admin only)
    Route::middleware('role:ADMIN')->group(function () {
        Route::apiResource('users', UserController::class);
    });

    // Settings (read-only for all authenticated users)
    Route::get('/settings', [SettingsController::class, 'index']);
    Route::get('/settings/sources', [SettingsController::class, 'getSources']);
    Route::get('/settings/courses', [SettingsController::class, 'getCourses']);
    Route::get('/settings/statuses', [SettingsController::class, 'getStatuses']);

    // Settings (Admin only - write operations)
    Route::middleware('role:ADMIN')->group(function () {
        Route::put('/settings/sources', [SettingsController::class, 'updateSources']);
        Route::put('/settings/courses', [SettingsController::class, 'updateCourses']);
        Route::put('/settings/statuses', [SettingsController::class, 'updateStatuses']);
    });

    // Webhooks (Admin only)
    Route::middleware('role:ADMIN')->group(function () {
        Route::apiResource('webhooks', WebhookController::class);
        Route::post('/webhooks/{id}/test', [WebhookController::class, 'test']);
    });

    // Activity Logs
    Route::get('/activity-logs', [ActivityLogController::class, 'index']);

    // Backup/Restore (Admin only)
    Route::middleware('role:ADMIN')->group(function () {
        Route::get('/backup', [BackupController::class, 'backup']);
        Route::post('/restore', [BackupController::class, 'restore']);
    });

    // Super Admin routes
    Route::middleware('role:SUPER_ADMIN')->prefix('admin')->group(function () {
        // Vendors
        Route::apiResource('vendors', VendorController::class);
        Route::post('/vendors/{id}/block', [VendorController::class, 'block']);
        Route::post('/vendors/{id}/activate', [VendorController::class, 'activate']);

        // Subscriptions
        Route::apiResource('subscriptions', SubscriptionController::class);
        Route::post('/subscriptions/{id}/cancel', [SubscriptionController::class, 'cancel']);
        Route::post('/subscriptions/{id}/renew', [SubscriptionController::class, 'renew']);
    });
});
