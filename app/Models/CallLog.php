<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CallLog extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'organization_id',
        'phone_number',
        'call_type',
        'call_status',
        'duration',
        'call_timestamp',
        'lead_id',
        'notes',
        'device_id',
    ];

    protected $casts = [
        'call_timestamp' => 'datetime',
        'duration' => 'integer',
    ];

    /**
     * Get the user (agent) who made/received the call
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the organization
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'organization_id');
    }

    /**
     * Get the matched lead (if any)
     */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    /**
     * Scope for filtering by organization
     */
    public function scopeForOrganization($query, $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }

    /**
     * Scope for filtering by date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('call_timestamp', [$startDate, $endDate]);
    }

    /**
     * Auto-match a lead by phone number
     */
    public static function matchLead(string $phoneNumber, string $organizationId): ?string
    {
        $normalized = Lead::normalizePhone($phoneNumber);
        if (!$normalized) {
            return null;
        }

        $lead = Lead::where('organization_id', $organizationId)
            ->where('phone_normalized', $normalized)
            ->first();

        return $lead?->id;
    }

    /**
     * Format duration as human-readable string
     */
    public function getFormattedDurationAttribute(): string
    {
        $minutes = intdiv($this->duration, 60);
        $seconds = $this->duration % 60;

        if ($minutes > 0) {
            return "{$minutes}m {$seconds}s";
        }
        return "{$seconds}s";
    }
}
