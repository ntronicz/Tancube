<?php

namespace App\Services;

use App\Models\User;
use App\Models\Webhook;
use App\Models\AppSetting;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class BackupService
{
    /**
     * Generate backup data array for an organization.
     */
    public function generateBackupData(string $organizationId): array
    {
        // Fetch Data
        $leads = \App\Models\Lead::where('organization_id', $organizationId)->get();
        $tasks = \App\Models\Task::where('organization_id', $organizationId)->get();
        $users = User::where('organization_id', $organizationId)->get()->makeVisible(['password', 'remember_token']);
        $webhooks = Webhook::where('organization_id', $organizationId)->get();
        
        $settings = [
            'general' => AppSetting::getForOrganization($organizationId, 'general'),
            'sources' => AppSetting::getSources($organizationId),
            'courses' => AppSetting::getCourses($organizationId),
            'statuses' => AppSetting::getStatuses($organizationId),
            'backup' => AppSetting::getForOrganization($organizationId, 'backup'), // Include backup settings
        ];

        return [
            'version' => '1.0',
            'timestamp' => now()->toIso8601String(),
            'organization_id' => $organizationId,
            'data' => [
                'leads' => $leads,
                'tasks' => $tasks,
                'users' => $users,
                'webhooks' => $webhooks,
                'settings' => $settings,
            ]
        ];
    }

    /**
     * Save backup data to storage.
     * Returns the relative path to the saved file.
     */
    public function saveBackup(string $organizationId, array $data): string
    {
        $orgName = \App\Models\Vendor::find($organizationId)->name ?? 'org';
        $filename = 'backup_' . Str::slug($orgName) . '_' . now()->format('Y-m-d_H-i-s') . '.json';
        $path = "backups/{$organizationId}/{$filename}";

        Storage::put($path, json_encode($data, JSON_PRETTY_PRINT));

        return $path;
    }
}
