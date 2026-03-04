<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use App\Models\Task;
use App\Channels\FcmChannel;

class TaskDue extends Notification
{
    public $task;

    public function __construct(Task $task)
    {
        $this->task = $task;
    }

    public function via(object $notifiable): array
    {
        return ['database', FcmChannel::class];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'task_id' => $this->task->id,
            'title' => $this->task->title,
            'due_date' => $this->task->due_date,
            'message' => 'Task Due: ' . $this->task->title,
            'type' => 'TASK_DUE',
        ];
    }

    public function toFcm(object $notifiable): array
    {
        return [
            'notification' => [
                'title' => 'Task Due',
                'body' => "Task is due: {$this->task->title}",
            ],
            'data' => [
                'type' => 'TASK_DUE',
                'task_id' => (string) $this->task->id,
            ],
        ];
    }
}
