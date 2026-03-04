<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, HasUuids;

    protected $fillable = [
        'organization_id',
        'name',
        'email',
        'password',
        'role',
        'avatar',
        'api_token',
        'fcm_token',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the identifier for JWT
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Get custom claims for JWT
     */
    public function getJWTCustomClaims()
    {
        return [
            'role' => $this->role,
            'organization_id' => $this->organization_id,
        ];
    }

    /**
     * Get the organization (vendor) this user belongs to
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'organization_id');
    }

    /**
     * Get leads assigned to this user
     */
    public function assignedLeads(): HasMany
    {
        return $this->hasMany(Lead::class, 'assigned_to');
    }

    /**
     * Get tasks assigned to this user
     */
    public function assignedTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }

    /**
     * Get tasks created by this user
     */
    public function createdTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'created_by');
    }

    /**
     * Get activity logs for this user
     */
    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    /**
     * Get call logs for this user
     */
    public function callLogs(): HasMany
    {
        return $this->hasMany(CallLog::class);
    }

    /**
     * Check if user is super admin
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'SUPER_ADMIN';
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'ADMIN';
    }

    /**
     * Check if user is agent
     */
    public function isAgent(): bool
    {
        return $this->role === 'AGENT';
    }

    /**
     * Route notifications for the FCM channel.
     *
     * @return string|array
     */
    public function routeNotificationForFcm()
    {
        // Return device tokens stored in database or related model
        // For now, return empty array as placeholder until device tokens are implemented
        return $this->fcm_token; 
    }

    /**
     * The channels the user receives notification broadcasts on.
     *
     * @return string
     */
    public function receivesBroadcastNotificationsOn()
    {
        return 'App.Models.User.' . $this->id;
    }
}
