<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'plan_name',
        'start_date',
        'expiry_date',
        'amount',
        'frequency',
        'status',
        'plan_id',
        'max_admins',
        'max_agents',
    ];

    protected $casts = [
        'start_date' => 'date',
        'expiry_date' => 'date',
        'amount' => 'decimal:2',
    ];

    /**
     * Get the vendor that owns this subscription
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * Get the plan that owns this subscription
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Check if subscription is active
     */
    public function isActive(): bool
    {
        return $this->status === 'ACTIVE' && $this->expiry_date >= now();
    }

    /**
     * Check if subscription is expired
     */
    public function isExpired(): bool
    {
        return $this->expiry_date < now();
    }
}
