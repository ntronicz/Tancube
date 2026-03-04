<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'organization_id',
        'title',
        'description',
        'due_date',
        'priority',
        'status',
        'assigned_to',
        'created_by',
        'alert_count',
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'alert_count' => 'integer',
    ];

    /**
     * Get the organization this task belongs to
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'organization_id');
    }

    /**
     * Get the user this task is assigned to
     */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the user who created this task
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope for pending tasks
     */
    public function scopePending($query)
    {
        return $query->where('status', 'PENDING');
    }

    /**
     * Scope for completed tasks
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'COMPLETED');
    }

    /**
     * Scope for high priority tasks
     */
    public function scopeHighPriority($query)
    {
        return $query->where('priority', 'HIGH');
    }

    /**
     * Scope for today's tasks
     */
    public function scopeDueToday($query)
    {
        return $query->whereDate('due_date', today());
    }

    /**
     * Scope for overdue tasks
     */
    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', today())
            ->where('status', 'PENDING');
    }

    /**
     * Scope for filtering by organization
     */
    public function scopeForOrganization($query, $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }

    /**
     * Scope for tasks visible to a user (based on role)
     */
    public function scopeVisibleToUser($query, User $user)
    {
        if ($user->isAdmin() || $user->isSuperAdmin()) {
            return $query;
        }

        // Agents can only see tasks assigned to them or created by them
        return $query->where(function ($q) use ($user) {
            $q->where('assigned_to', $user->id)
              ->orWhere('created_by', $user->id);
        });
    }

    /**
     * Check if task is overdue
     */
    public function isOverdue(): bool
    {
        return $this->due_date < today() && $this->status === 'PENDING';
    }

    /**
     * Get priority color for UI
     */
    public function getPriorityColorAttribute(): string
    {
        return match ($this->priority) {
            'HIGH' => 'red',
            'MEDIUM' => 'yellow',
            'LOW' => 'green',
            default => 'gray',
        };
    }
}
