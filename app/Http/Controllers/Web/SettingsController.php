<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AppSetting;
use App\Models\ActivityLog;
use App\Models\Webhook;
use App\Models\Subscription;
use App\Notifications\LimitReachedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class SettingsController extends Controller
{
    /**
     * Show settings page
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $organizationId = $user->organization_id;
        $tab = $request->input('tab', 'general');
        $isSuperAdmin = $user->role === 'SUPER_ADMIN';

        // General - use defaults if Super Admin
        $sources = AppSetting::getSources($organizationId);
        $courses = AppSetting::getCourses($organizationId);
        $statuses = AppSetting::getStatuses($organizationId);
        
        $generalSettings = AppSetting::getForOrganization($organizationId, 'general') ?? [];
        if (!isset($generalSettings['timezone'])) {
            $generalSettings['timezone'] = 'Asia/Kolkata';
        }

        // Users - Super Admin sees all users
        if ($isSuperAdmin) {
            $users = User::with('organization')->get();
            $webhooks = Webhook::all();
        } else {
            $users = User::where('organization_id', $organizationId)->get();
            $webhooks = Webhook::where('organization_id', $organizationId)->get();
        }

        // Activity Logs with Filtering
        $activityQuery = ActivityLog::query();
        if (!$isSuperAdmin) {
            $activityQuery->where('organization_id', $organizationId);
        }
        
        if ($request->filled('action')) {
            $activityQuery->where('action', $request->action);
        }
        
        if ($request->filled('start_date')) {
            $activityQuery->whereDate('timestamp', '>=', $request->start_date);
        }
        
        if ($request->filled('end_date')) {
            $activityQuery->whereDate('timestamp', '<=', $request->end_date);
        }

        $activityLogs = $activityQuery->with('user:id,name')
            ->orderBy('timestamp', 'desc')
            ->paginate(100)
            ->appends(request()->query());
            
        // Get unique actions for filter dropdown
        $actions = ActivityLog::distinct()->pluck('action');

        return view('settings.index', compact(
            'tab',
            'sources',
            'courses',
            'statuses',
            'users',
            'activityLogs',
            'webhooks',
            'generalSettings',
            'actions'
        ));
    }

    /**
     * Update logged-in user profile
     */
    public function updateProfile(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }
        $user->save();

        ActivityLog::log('PROFILE_UPDATED', 'Updated profile details');

        return back()->with('success', 'Profile updated successfully!');
    }

    /**
     * Update user (Admin only)
     */
    public function updateUser(Request $request, string $id)
    {
        $admin = Auth::user();
        if ($admin->role !== 'ADMIN' && $admin->role !== 'SUPER_ADMIN') {
            abort(403);
        }

        $user = User::findOrFail($id);
        
        // Ensure admin belongs to same org (unless super admin)
        if ($admin->role === 'ADMIN' && $user->organization_id !== $admin->organization_id) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8',
            'role' => 'required|in:AGENT,ADMIN',
        ]);

        if ($user->role !== $request->role) {
            $subscription = Subscription::where('vendor_id', $user->organization_id)
                ->where('status', 'ACTIVE')
                ->first();

            if (!$subscription) {
                return back()->with('error', 'No active subscription found. Cannot change role.');
            }

            if ($request->role === 'ADMIN') {
                $adminCount = User::where('organization_id', $user->organization_id)->where('role', 'ADMIN')->count();
                if ($adminCount >= $subscription->max_admins) {
                    $adminsToNotify = User::where('organization_id', $user->organization_id)->where('role', 'ADMIN')->get();
                    Notification::send($adminsToNotify, new LimitReachedNotification('Admin', $subscription->max_admins));
                    return back()->with('error', "Cannot assign ADMIN role. Maximum limit of {$subscription->max_admins} Admins reached.");
                }
            } else if ($request->role === 'AGENT') {
                $agentCount = User::where('organization_id', $user->organization_id)->where('role', 'AGENT')->count();
                if ($agentCount >= $subscription->max_agents) {
                    $adminsToNotify = User::where('organization_id', $user->organization_id)->where('role', 'ADMIN')->get();
                    Notification::send($adminsToNotify, new LimitReachedNotification('Agent', $subscription->max_agents));
                    return back()->with('error', "Cannot assign AGENT role. Maximum limit of {$subscription->max_agents} Agents reached.");
                }
            }
        }

        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = $request->role;
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }
        $user->save();

        ActivityLog::log('USER_UPDATED', "Updated user: {$user->name}");

        return back()->with('success', 'User updated successfully!');
    }
    
    /**
     * Clear activity logs (with filters)
     */
    public function clearLogs(Request $request)
    {
        $user = Auth::user();
        // Ensure only ADMIN or SUPER_ADMIN can clear logs
        if ($user->role !== 'ADMIN' && $user->role !== 'SUPER_ADMIN') {
            abort(403);
        }

        $query = ActivityLog::query();

        // Super Admin can clear all logs, others only their organization's logs
        if ($user->role !== 'SUPER_ADMIN') {
            $query->where('organization_id', $user->organization_id);
        }

        // Apply filters if present
        if ($request->has('action') && $request->action) {
            $query->where('action', $request->action);
        }

        if ($request->has('start_date') && $request->start_date) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->has('end_date') && $request->end_date) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $count = $query->delete();
        ActivityLog::log('LOGS_CLEARED', "Cleared {$count} activity logs");

        return back()->with('success', "Cleared {$count} activity logs successfully!");
    }

    /**
     * Generate/Regenerate API Token
     */
    public function generateApiToken()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if ($user->role !== 'ADMIN' && $user->role !== 'SUPER_ADMIN') {
            abort(403);
        }

        $user->api_token = Str::random(60);
        $user->save();

        ActivityLog::log('API_TOKEN_GENERATED', 'Generated new API token');

        return back()->with('success', 'API Token generated successfully!');
    }

    /**
     * Update general settings
     */
    public function updateGeneral(Request $request)
    {
        $request->validate([
            'timezone' => 'required|string',
        ]);

        $user = Auth::user();
        
        // Fetch existing settings to merge or create new
        $currentSettings = AppSetting::getForOrganization($user->organization_id, 'general') ?? [];
        $currentSettings['timezone'] = $request->timezone;
        
        AppSetting::setForOrganization($user->organization_id, 'general', $currentSettings);

        ActivityLog::log('SETTINGS_UPDATED', 'Updated general settings (timezone)');

        return back()->with('success', 'General settings updated successfully!');
    }

    /**
     * Update sources
     */
    public function updateSources(Request $request)
    {
        $request->validate([
            'sources' => 'required|string',
        ]);

        $user = Auth::user();
        $sources = array_filter(array_map('trim', explode("\n", $request->sources)));
        AppSetting::setForOrganization($user->organization_id, 'sources', $sources);

        ActivityLog::log('SETTINGS_UPDATED', 'Updated lead sources');

        return back()->with('success', 'Sources updated successfully!');
    }

    /**
     * Update courses
     */
    public function updateCourses(Request $request)
    {
        $request->validate([
            'courses' => 'required|string',
        ]);

        $user = Auth::user();
        $courses = array_filter(array_map('trim', explode("\n", $request->courses)));
        AppSetting::setForOrganization($user->organization_id, 'courses', $courses);

        ActivityLog::log('SETTINGS_UPDATED', 'Updated courses');

        return back()->with('success', 'Courses updated successfully!');
    }

    /**
     * Update statuses
     */
    public function updateStatuses(Request $request)
    {
        $request->validate([
            'statuses' => 'required|string',
        ]);

        $user = Auth::user();
        $statuses = array_filter(array_map('trim', explode("\n", $request->statuses)));
        AppSetting::setForOrganization($user->organization_id, 'statuses', $statuses);

        ActivityLog::log('SETTINGS_UPDATED', 'Updated lead statuses');

        return back()->with('success', 'Statuses updated successfully!');
    }

    /**
     * Store new user
     */
    public function storeUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => 'required|in:ADMIN,AGENT',
        ]);

        $user = Auth::user();

        // Enforce Subscription Limits
        $subscription = Subscription::where('vendor_id', $user->organization_id)
            ->where('status', 'ACTIVE')
            ->first();

        if (!$subscription) {
            return back()->with('error', 'No active subscription found. Cannot add users.');
        }

        if ($request->role === 'ADMIN') {
            $adminCount = User::where('organization_id', $user->organization_id)->where('role', 'ADMIN')->count();
            if ($adminCount >= $subscription->max_admins) {
                // Determine admins to notify
                $adminsToNotify = User::where('organization_id', $user->organization_id)->where('role', 'ADMIN')->get();
                Notification::send($adminsToNotify, new LimitReachedNotification('Admin', $subscription->max_admins));

                return back()->with('error', "Maximum limit of {$subscription->max_admins} Admins reached. Please upgrade your plan.");
            }
        } else if ($request->role === 'AGENT') {
            $agentCount = User::where('organization_id', $user->organization_id)->where('role', 'AGENT')->count();
            if ($agentCount >= $subscription->max_agents) {
                $adminsToNotify = User::where('organization_id', $user->organization_id)->where('role', 'ADMIN')->get();
                Notification::send($adminsToNotify, new LimitReachedNotification('Agent', $subscription->max_agents));

                return back()->with('error', "Maximum limit of {$subscription->max_agents} Agents reached. Please upgrade your plan.");
            }
        }

        User::create([
            'organization_id' => $user->organization_id,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        ActivityLog::log('USER_CREATED', "Created user: {$request->name}");

        return back()->with('success', 'User created successfully!');
    }

    /**
     * Delete user
     */
    public function destroyUser(string $id)
    {
        $admin = Auth::user();
        $query = User::where('id', $id);

        // Non-super-admins can only delete users within their own organization
        if ($admin->role !== 'SUPER_ADMIN') {
            $query->where('organization_id', $admin->organization_id);
        }

        $user = $query->firstOrFail();

        if ($user->id === $admin->id) {
            return back()->with('error', 'Cannot delete yourself.');
        }

        $userName = $user->name;
        $user->delete();

        ActivityLog::log('USER_DELETED', "Deleted user: {$userName}");

        return back()->with('success', 'User deleted successfully!');
    }

    /**
     * Store webhook
     */
    public function storeWebhook(Request $request)
    {
        $request->validate([
            'url' => 'required|url|max:500',
            'events' => 'required|array',
        ]);

        $user = Auth::user();

        Webhook::create([
            'organization_id' => $user->organization_id,
            'url' => $request->url,
            'events' => $request->events,
            'is_active' => true,
        ]);

        ActivityLog::log('WEBHOOK_CREATED', "Created webhook: {$request->url}");

        return back()->with('success', 'Webhook created successfully!');
    }

    /**
     * Delete webhook
     */
    public function destroyWebhook(string $id)
    {
        $webhook = Webhook::findOrFail($id);
        $url = $webhook->url;
        $webhook->delete();

        ActivityLog::log('WEBHOOK_DELETED', "Deleted webhook: {$url}");

        return back()->with('success', 'Webhook deleted successfully!');
    }
    /**
     * Download Backup
     */
    public function downloadBackup(\App\Services\BackupService $backupService)
    {
        $user = Auth::user();
        if ($user->role !== 'ADMIN' && $user->role !== 'SUPER_ADMIN') {
            abort(403);
        }

        $organizationId = $user->organization_id;
        $data = $backupService->generateBackupData($organizationId);

        $filename = 'backup_' . Str::slug($user->organization->name ?? 'org') . '_' . now()->format('Y-m-d_H-i') . '.json';
        
        return response()->streamDownload(function () use ($data) {
            echo json_encode($data, JSON_PRETTY_PRINT);
        }, $filename, ['Content-Type' => 'application/json']);
    }

    /**
     * Update Automated Backup Settings
     */
    public function updateBackupSettings(Request $request)
    {
        $request->validate([
            'frequency' => 'required|in:never,daily,weekly,monthly',
        ]);

        $user = Auth::user();
        // Check permissions
        if ($user->role !== 'ADMIN' && $user->role !== 'SUPER_ADMIN') {
             abort(403);
        }

        $settings = AppSetting::getForOrganization($user->organization_id, 'backup') ?? [];
        $settings['frequency'] = $request->frequency;
        // Reset last backup timestamp if frequency changed? Not strictly necessary but clean.
        
        AppSetting::setForOrganization($user->organization_id, 'backup', $settings);

        ActivityLog::log('SETTINGS_UPDATED', "Updated auto-backup frequency to: {$request->frequency}");

        return back()->with('success', 'Backup settings updated successfully!');
    }

    /**
     * Restore Backup
     */
    public function restoreBackup(Request $request)
    {
        $user = Auth::user();
        if ($user->role !== 'ADMIN' && $user->role !== 'SUPER_ADMIN') {
            abort(403);
        }

        $request->validate([
            'backup_file' => 'required|file|mimetypes:application/json,text/plain',
        ]);

        try {
            $jsonContent = file_get_contents($request->file('backup_file')->getRealPath());
            $backup = json_decode($jsonContent, true);

            if (!$backup || !isset($backup['organization_id']) || !isset($backup['data'])) {
                return back()->with('error', 'Invalid backup file format.');
            }

            // Security Check overrides for Super Admin if needed, but per request restrict to same org
            if ($backup['organization_id'] !== $user->organization_id) {
                return back()->with('error', 'Cannot restore backup from a different organization.');
            }

            \Illuminate\Support\Facades\DB::transaction(function () use ($backup, $user) {
                $orgId = $user->organization_id;
                $data = $backup['data'];

                // Restore Settings
                if (isset($data['settings']) && is_array($data['settings'])) {
                    foreach ($data['settings'] as $key => $value) {
                        // Ensure value is an array, as setForOrganization expects array
                        // If value is null (e.g. settings not set), use empty array or skip
                        $settingsValue = is_array($value) ? $value : [];
                        AppSetting::setForOrganization($orgId, $key, $settingsValue);
                    }
                }

                // Restore Users (Update existing, Create new) — whitelist fields only
                if (isset($data['users'])) {
                    foreach ($data['users'] as $userData) {
                        // Only allow safe, whitelisted fields
                        $allowedRoles = ['ADMIN', 'AGENT'];
                        $role = (isset($userData['role']) && in_array($userData['role'], $allowedRoles))
                            ? $userData['role'] : 'AGENT';

                        $userPayload = [
                            'organization_id' => $orgId,
                            'name' => $userData['name'] ?? 'Unknown',
                            'role' => $role,
                        ];

                        // Only set password for new users (never overwrite existing passwords from backup)
                        if (!User::where('email', $userData['email'] ?? '')->exists()) {
                            $userPayload['password'] = Hash::make('password');
                        }

                        User::updateOrCreate(
                            ['email' => $userData['email']], // Match by unique email
                            $userPayload
                        );
                    }
                }

                // Restore Leads — whitelist fields only
                if (isset($data['leads'])) {
                    $allowedLeadFields = ['name', 'phone', 'phone_normalized', 'email', 'source', 'course', 'status', 'assigned_to', 'notes', 'next_follow_up', 'last_contacted'];
                    foreach ($data['leads'] as $leadData) {
                        $safeData = array_intersect_key($leadData, array_flip($allowedLeadFields));
                        $safeData['organization_id'] = $orgId;
                        \App\Models\Lead::updateOrCreate(
                            ['id' => $leadData['id']],
                            $safeData
                        );
                    }
                }

                // Restore Tasks — whitelist fields only
                if (isset($data['tasks'])) {
                    $allowedTaskFields = ['title', 'description', 'status', 'priority', 'assigned_to', 'created_by', 'due_date'];
                    foreach ($data['tasks'] as $taskData) {
                        $safeData = array_intersect_key($taskData, array_flip($allowedTaskFields));
                        $safeData['organization_id'] = $orgId;
                        \App\Models\Task::updateOrCreate(
                            ['id' => $taskData['id']],
                            $safeData
                        );
                    }
                }

                // Restore Webhooks — whitelist fields only
                if (isset($data['webhooks'])) {
                    $allowedWebhookFields = ['url', 'events', 'is_active'];
                    foreach ($data['webhooks'] as $webhookData) {
                        $safeData = array_intersect_key($webhookData, array_flip($allowedWebhookFields));
                        $safeData['organization_id'] = $orgId;
                        Webhook::updateOrCreate(
                            ['id' => $webhookData['id']],
                            $safeData
                        );
                    }
                }
            });
            
            ActivityLog::log('SYSTEM_RESTORE', 'Restored system from backup');

            return back()->with('success', 'System restored successfully! Data has been updated.');

        } catch (\Exception $e) {
            return back()->with('error', 'Restore failed: ' . $e->getMessage());
        }
    }

    /**
     * Mark all notifications as read
     */
    public function markNotificationsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();
        return back();
    }
}
