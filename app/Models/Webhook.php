<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Http;

class Webhook extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'url',
        'events',
        'is_active',
    ];

    protected $casts = [
        'events' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get the organization this webhook belongs to
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'organization_id');
    }

    /**
     * Check if webhook should trigger for an event
     */
    public function shouldTrigger(string $event): bool
    {
        if (!$this->is_active) {
            return false;
        }

        return in_array($event, $this->events ?? []);
    }

    /**
     * Trigger the webhook
     */
    public function trigger(string $event, array $data): bool
    {
        if (!$this->shouldTrigger($event)) {
            return false;
        }

        try {
            $response = Http::timeout(10)->post($this->url, [
                'event' => $event,
                'data' => $data,
                'timestamp' => now()->toISOString(),
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Trigger webhooks for an organization
     */
    public static function triggerForOrganization(
        string $organizationId,
        string $event,
        array $data
    ): void {
        $webhooks = static::where('organization_id', $organizationId)
            ->where('is_active', true)
            ->get();

        foreach ($webhooks as $webhook) {
            $webhook->trigger($event, $data);
        }
    }

    /**
     * Scope for filtering by organization
     */
    public function scopeForOrganization($query, $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }
}
