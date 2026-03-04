<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use App\Models\Lead;
use App\Channels\FcmChannel;

class FollowUpReminder extends Notification
{
    public $lead;
    public $followUpDate;

    public function __construct(Lead $lead, $followUpDate)
    {
        $this->lead = $lead;
        $this->followUpDate = $followUpDate;
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
            'follow_up_date' => $this->followUpDate,
            'message' => 'Upcoming Follow-up with ' . $this->lead->name,
            'type' => 'FOLLOW_UP_REMINDER',
        ];
    }

    public function toFcm(object $notifiable): array
    {
        return [
            'notification' => [
                'title' => 'Follow-up Reminder',
                'body' => "Upcoming follow-up with {$this->lead->name}",
            ],
            'data' => [
                'type' => 'FOLLOW_UP_REMINDER',
                'lead_id' => (string) $this->lead->id,
            ],
        ];
    }
}
