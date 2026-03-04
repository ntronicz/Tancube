<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vendor extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'status',
        'is_deleted',
    ];

    protected $casts = [
        'is_deleted' => 'boolean',
    ];

    /**
     * Get users belonging to this vendor (organization)
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'organization_id');
    }

    /**
     * Get subscriptions for this vendor
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Get leads for this organization
     */
    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class, 'organization_id');
    }

    /**
     * Get tasks for this organization
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'organization_id');
    }

    /**
     * Get activity logs for this organization
     */
    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class, 'organization_id');
    }

    /**
     * Get webhooks for this organization
     */
    public function webhooks(): HasMany
    {
        return $this->hasMany(Webhook::class, 'organization_id');
    }

    /**
     * Get app settings for this organization
     */
    public function appSettings(): HasMany
    {
        return $this->hasMany(AppSetting::class, 'organization_id');
    }

    /**
     * Check if vendor has active subscription
     */
    public function hasActiveSubscription(): bool
    {
        return $this->subscriptions()
            ->where('status', 'ACTIVE')
            ->whereDate('expiry_date', '>=', now())
            ->exists();
    }

    /**
     * Get active subscription
     */
    public function activeSubscription()
    {
        return $this->subscriptions()
            ->where('status', 'ACTIVE')
            ->whereDate('expiry_date', '>=', now())
            ->first();
    }
}
