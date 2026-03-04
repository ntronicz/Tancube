<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\Task;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Subscription;
use App\Models\ActivityLog;
use App\Models\Webhook;
use App\Models\AppSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BackupController extends Controller
{
    /**
     * Export all data as JSON backup
     */
    public function backup(Request $request)
    {
        $user = Auth::user();

        if ($user->isSuperAdmin()) {
            // Super admin gets everything
            $data = [
                'vendors' => Vendor::all(),
                'subscriptions' => Subscription::all(),
                'users' => User::all()->makeHidden(['password']),
                'leads' => Lead::all(),
                'tasks' => Task::all(),
                'activity_logs' => ActivityLog::all(),
                'webhooks' => Webhook::all(),
                'app_settings' => AppSetting::all(),
            ];
        } else {
            // Organization admin gets their organization's data
            $orgId = $user->organization_id;
            $data = [
                'users' => User::where('organization_id', $orgId)->get()->makeHidden(['password']),
                'leads' => Lead::where('organization_id', $orgId)->get(),
                'tasks' => Task::where('organization_id', $orgId)->get(),
                'activity_logs' => ActivityLog::where('organization_id', $orgId)->get(),
                'webhooks' => Webhook::where('organization_id', $orgId)->get(),
                'app_settings' => AppSetting::where('organization_id', $orgId)->get(),
            ];
        }

        $data['exported_at'] = now()->toISOString();
        $data['exported_by'] = $user->email;

        ActivityLog::log('BACKUP_CREATED', 'Created data backup');

        $filename = 'backup_' . date('Y-m-d_H-i-s') . '.json';

        return response()->json($data)
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }

    /**
     * Restore data from JSON backup
     */
    public function restore(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:json|max:51200',
        ]);

        $user = Auth::user();
        $file = $request->file('file');
        $content = file_get_contents($file->getPathname());
        $data = json_decode($content, true);

        if (!$data) {
            return response()->json(['message' => 'Invalid JSON file'], 400);
        }

        DB::beginTransaction();

        try {
            $restored = [];

            // Restore leads
            if (isset($data['leads']) && is_array($data['leads'])) {
                foreach ($data['leads'] as $lead) {
                    // Skip if organization doesn't match (for non-super admin)
                    if (!$user->isSuperAdmin() && ($lead['organization_id'] ?? null) !== $user->organization_id) {
                        continue;
                    }

                    unset($lead['created_at'], $lead['updated_at']);
                    Lead::updateOrCreate(['id' => $lead['id']], $lead);
                }
                $restored['leads'] = count($data['leads']);
            }

            // Restore tasks
            if (isset($data['tasks']) && is_array($data['tasks'])) {
                foreach ($data['tasks'] as $task) {
                    if (!$user->isSuperAdmin() && ($task['organization_id'] ?? null) !== $user->organization_id) {
                        continue;
                    }

                    unset($task['created_at'], $task['updated_at']);
                    Task::updateOrCreate(['id' => $task['id']], $task);
                }
                $restored['tasks'] = count($data['tasks']);
            }

            // Restore app settings
            if (isset($data['app_settings']) && is_array($data['app_settings'])) {
                foreach ($data['app_settings'] as $setting) {
                    if (!$user->isSuperAdmin() && ($setting['organization_id'] ?? null) !== $user->organization_id) {
                        continue;
                    }

                    unset($setting['created_at'], $setting['updated_at']);
                    AppSetting::updateOrCreate(
                        ['organization_id' => $setting['organization_id'], 'category' => $setting['category']],
                        ['values' => $setting['values']]
                    );
                }
                $restored['app_settings'] = count($data['app_settings']);
            }

            // Super admin can restore vendors and subscriptions
            if ($user->isSuperAdmin()) {
                if (isset($data['vendors']) && is_array($data['vendors'])) {
                    foreach ($data['vendors'] as $vendor) {
                        unset($vendor['created_at'], $vendor['updated_at']);
                        Vendor::updateOrCreate(['id' => $vendor['id']], $vendor);
                    }
                    $restored['vendors'] = count($data['vendors']);
                }

                if (isset($data['subscriptions']) && is_array($data['subscriptions'])) {
                    foreach ($data['subscriptions'] as $subscription) {
                        unset($subscription['created_at'], $subscription['updated_at']);
                        Subscription::updateOrCreate(['id' => $subscription['id']], $subscription);
                    }
                    $restored['subscriptions'] = count($data['subscriptions']);
                }
            }

            DB::commit();

            ActivityLog::log('BACKUP_RESTORED', 'Restored data from backup: ' . json_encode($restored));

            return response()->json([
                'message' => 'Data restored successfully',
                'restored' => $restored,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Restore failed: ' . $e->getMessage()], 500);
        }
    }
}
