<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DispatchWebhooks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:dispatch-webhooks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatch time-based webhooks (currently unused)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Time-based webhook events have been removed.
        // Webhooks are now triggered directly from controllers on lead/task actions.
        $this->info('No time-based webhook events to dispatch.');
    }
}
