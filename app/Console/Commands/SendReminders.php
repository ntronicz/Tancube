<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Lead;
use App\Models\Task;
use App\Models\User;
use App\Notifications\FollowUpReminder;
use App\Notifications\TaskDue;
use Carbon\Carbon;

class SendReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crm:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send notifications for upcoming follow-ups and due tasks';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Starting reminder check...");

        // 1. Follow-up Reminders (Due in next 30 minutes)
        // Check for leads with follow_up between now and now + 30 mins
        $upcomingFollowUps = Lead::whereNotNull('next_follow_up')
            ->whereNotNull('assigned_to')
            ->where('next_follow_up', '<=', now()->addMinutes(30))
            // Prevent duplicate notifications
            ->where('follow_up_alert_count', 0) 
            ->get();

        foreach ($upcomingFollowUps as $lead) {
            /** @var \App\Models\Lead $lead */
            $user = User::find($lead->assigned_to);
            if ($user) {
                $user->notify(new FollowUpReminder($lead, $lead->next_follow_up));
                $this->info("Sent FollowUpReminder for Lead: {$lead->id} to User: {$user->id}");
                
                // Mark as alerted
                $lead->increment('follow_up_alert_count');
            }
        }

        // 2. Tasks Due Today
        // logic: Runs once a day, e.g. at 9 AM via Scheduler.
        // Or if running frequently, check if current time is around 9 AM?
        // Using Scheduler in console.php is better for "once daily".
        // BUT if this command runs minutely, we can't do daily logic easily unless we check time.
        // Separate command is cleaner? Or arguments.
        // Let's check for 'crm:send-reminders --daily' or similar?
        // For simplicity: This command handles "Timely" stuff. 
        // Tasks due TODAY should be notified. 
        // Let's say we check tasks due today that haven't been alerted.
        // Task model needs 'alerted_at' or similar. 
        // If we don't have that column, we might spam.
        // ALTERNATIVE: taskDue notification sends instantly when due date arrives?
        // Or just at 9 AM.
        // If this command runs every minute, we can check if time() is 09:00.
        
        $currentHour = now()->format('H:i');
        if ($currentHour === '09:00') {
             $todaysTasks = Task::whereDate('due_date', today())
                ->where('status', '!=', 'COMPLETED')
                ->whereNotNull('assigned_to')
                ->get();

             foreach ($todaysTasks as $task) {
                 $user = User::find($task->assigned_to);
                 if ($user) {
                     $user->notify(new TaskDue($task));
                     $this->info("Sent TaskDue for Task: {$task->id}");
                 }
             }
        }

        $this->info("Reminder check complete.");
    }
}
