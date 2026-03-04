<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use App\Models\Lead;
use App\Channels\FcmChannel;

class LeadAssigned extends Notification
{
    public $lead;

    public function __construct(Lead $lead)
    {
        $this->lead = $lead;
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
            'message' => 'New Lead Assigned: ' . $this->lead->name,
            'type' => 'LEAD_ASSIGNED',
        ];
    }

    public function toFcm(object $notifiable): array
    {
        return [
            'notification' => [
                'title' => 'New Lead Assigned',
                'body' => "Lead assigned to you: {$this->lead->name}",
            ],
            'data' => [
                'type' => 'LEAD_ASSIGNED',
                'lead_id' => (string) $this->lead->id,
            ],
        ];
    }
}
