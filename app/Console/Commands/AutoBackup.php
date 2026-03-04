<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AppSetting;
use App\Services\BackupService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AutoBackup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:auto-backup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run automated backups for organizations based on their settings';

    /**
     * Execute the console command.
     */
    public function handle(BackupService $backupService)
    {
        $this->info('Starting automated backup process...');

        // 1. Get all backup settings
        // Since settings are stored in JSON 'values' column, filtering by JSON value in SQL can be tricky 
        // depending on DB. For simplicity and since we likely don't have millions of orgs yet, 
        // we can fetch all 'backup' category settings.
        
        $backupSettings = AppSetting::where('category', 'backup')->get();

        /** @var \App\Models\AppSetting $setting */
        foreach ($backupSettings as $setting) {
            $orgId = $setting->organization_id;
            $config = $setting->values;
            $frequency = $config['frequency'] ?? 'never';
            
            if ($frequency === 'never') {
                continue;
            }

            $lastBackup = isset($config['last_backup_at']) ? Carbon::parse($config['last_backup_at']) : null;
            $shouldRun = false;
            $now = Carbon::now();

            switch ($frequency) {
                case 'daily':
                    // Run if no last backup OR last backup was before today (yesterday or earlier)
                    $shouldRun = !$lastBackup || $lastBackup->isBefore($now->copy()->startOfDay());
                    break;
                case 'weekly':
                    // Run if no last backup OR last backup was at least 7 days ago
                    // Or simpler: check if it's the start of the week? 
                    // Better: check if last backup was more than 6 days ago.
                    $shouldRun = !$lastBackup || $lastBackup->diffInDays($now) >= 7;
                    break;
                case 'monthly':
                    // Run if no last backup OR last backup was in previous month
                    $shouldRun = !$lastBackup || $lastBackup->isBefore($now->copy()->startOfMonth());
                    break;
            }

            if ($shouldRun) {
                $this->info("Backing up Organization ID: {$orgId} ({$frequency})");
                try {
                    $data = $backupService->generateBackupData($orgId);
                    $path = $backupService->saveBackup($orgId, $data);
                    
                    // Update last_backup_at
                    $config['last_backup_at'] = $now->toIso8601String();
                    $setting->values = $config;
                    $setting->save();
                    
                    $this->info("Backup saved to: {$path}");
                    Log::info("Auto-backup success for Org {$orgId}: {$path}");
                } catch (\Exception $e) {
                    $this->error("Backup failed for Org {$orgId}: " . $e->getMessage());
                    Log::error("Auto-backup failed for Org {$orgId}: " . $e->getMessage());
                }
            }
        }

        $this->info('Automated backup process completed.');
    }
}
