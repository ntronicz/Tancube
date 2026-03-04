<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Task;
use App\Channels\FcmChannel;

class TaskCreated extends Notification
{
    public $task;
    public $createdByName;

    public function __construct(Task $task, string $createdByName)
    {
        $this->task = $task;
        $this->createdByName = $createdByName;
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
            'created_by' => $this->createdByName,
            'due_date' => $this->task->due_date,
            'priority' => $this->task->priority,
            'message' => "New Task: '{$this->task->title}' assigned by {$this->createdByName}",
            'type' => 'TASK_CREATED',
        ];
    }

    public function toFcm(object $notifiable): array
    {
        return [
            'notification' => [
                'title' => 'New Task Assigned',
                'body' => "{$this->createdByName} assigned: {$this->task->title}",
            ],
            'data' => [
                'type' => 'TASK_CREATED',
                'task_id' => (string) $this->task->id,
            ],
        ];
    }
}
