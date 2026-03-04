<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lead extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'organization_id',
        'name',
        'phone',
        'phone_normalized',
        'email',
        'source',
        'course',
        'status',
        'assigned_to',
        'notes',
        'next_follow_up',
        'last_contacted',
        'follow_up_alert_count',
        'date_converted',
    ];

    protected $casts = [
        'next_follow_up' => 'datetime',
        'last_contacted' => 'datetime',
        'follow_up_alert_count' => 'integer',
        'date_converted' => 'datetime',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-normalize phone number on save
        static::saving(function ($lead) {
            if ($lead->phone) {
                $lead->phone_normalized = static::normalizePhone($lead->phone);
            }
        });
    }

    /**
     * Normalize phone number - remove symbols, keep last 10 digits
     */
    public static function normalizePhone(?string $phone): ?string
    {
        if (!$phone) {
            return null;
        }

        // Remove all non-digit characters
        $digits = preg_replace('/\D/', '', $phone);

        // Keep last 10 digits
        if (strlen($digits) >= 10) {
            return substr($digits, -10);
        }

        return $digits;
    }

    /**
     * Get the organization this lead belongs to
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'organization_id');
    }

    /**
     * Get the user this lead is assigned to
     */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Scope for pending follow-ups
     */
    public function scopePendingFollowUps($query)
    {
        return $query->whereNotNull('next_follow_up')
            ->where('next_follow_up', '<=', now())
            ->where('status', '!=', 'CONVERTED')
            ->where('status', '!=', 'LOST');
    }

    /**
     * Scope for today's follow-ups
     */
    public function scopeTodayFollowUps($query)
    {
        return $query->whereNotNull('next_follow_up')
            ->whereDate('next_follow_up', today());
    }

    /**
     * Scope for filtering by organization
     */
    public function scopeForOrganization($query, $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }

    /**
     * Scope for searching leads - partial, case-insensitive across Name, Phone, Email
     */
    public function scopeSearch($query, $term)
    {
        if (empty($term)) {
            return $query;
        }

        $escaped = str_replace(['%', '_'], ['\\%', '\\_'], $term);
        $safeTerm = '%' . $escaped . '%';

        return $query->where(function ($q) use ($safeTerm) {
            $q->where('name', 'like', $safeTerm)
              ->orWhere('phone', 'like', $safeTerm)
              ->orWhere('phone_normalized', 'like', $safeTerm)
              ->orWhere('email', 'like', $safeTerm);
        });
    }

    /**
     * Check if lead has pending follow-up
     */
    public function hasPendingFollowUp(): bool
    {
        return $this->next_follow_up && $this->next_follow_up <= now();
    }
}
