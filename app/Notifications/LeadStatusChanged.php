<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use App\Models\Lead;
use App\Channels\FcmChannel;

class LeadStatusChanged extends Notification
{
    public $lead;
    public $oldStatus;
    public $changedBy;

    public function __construct(Lead $lead, string $oldStatus, string $changedBy)
    {
        $this->lead = $lead;
        $this->oldStatus = $oldStatus;
        $this->changedBy = $changedBy;
    }

    public function via(object $notifiable): array
    {
        return ['database', FcmChannel::class];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'lead_id' => $this->lead->id,
            'name' => $this->lead->name,
            'old_status' => $this->oldStatus,
            'new_status' => $this->lead->status,
            'changed_by' => $this->changedBy,
            'message' => "Lead '{$this->lead->name}' status changed from {$this->oldStatus} to {$this->lead->status}",
            'type' => 'LEAD_STATUS_CHANGED',
        ];
    }

    public function toFcm(object $notifiable): array
    {
        return [
            'notification' => [
                'title' => 'Lead Status Updated',
                'body' => "{$this->lead->name} changed to {$this->lead->status}",
            ],
            'data' => [
                'type' => 'LEAD_STATUS_CHANGED',
                'lead_id' => (string) $this->lead->id,
            ],
        ];
    }
}
