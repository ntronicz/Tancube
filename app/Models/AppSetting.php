<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'category',
        'values',
    ];

    protected $casts = [
        'values' => 'array',
    ];

    /**
     * Get the organization this setting belongs to
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'organization_id');
    }

    /**
     * Get setting by category for an organization
     */
    public static function getForOrganization(?string $organizationId, string $category): ?array
    {
        if (!$organizationId) {
            return null;
        }

        $setting = static::where('organization_id', $organizationId)
            ->where('category', $category)
            ->first();

        return $setting?->values;
    }

    /**
     * Set values for a category
     */
    public static function setForOrganization(
        string $organizationId,
        string $category,
        array $values
    ): self {
        return static::updateOrCreate(
            [
                'organization_id' => $organizationId,
                'category' => $category,
            ],
            ['values' => $values]
        );
    }

    /**
     * Get sources for an organization
     */
    public static function getSources(?string $organizationId): array
    {
        return static::getForOrganization($organizationId, 'sources') ?? [
            'Facebook',
            'Google',
            'Website',
            'Referral',
            'Walk-in',
            'Other',
        ];
    }

    /**
     * Get courses for an organization
     */
    public static function getCourses(?string $organizationId): array
    {
        return static::getForOrganization($organizationId, 'courses') ?? [
            'MBA',
            'BBA',
            'Engineering',
            'Medical',
            'Arts',
            'Commerce',
            'Other',
        ];
    }

    /**
     * Get statuses for an organization
     */
    public static function getStatuses(?string $organizationId): array
    {
        return static::getForOrganization($organizationId, 'statuses') ?? [
            'NEW',
            'CONTACTED',
            'QUALIFIED',
            'NEGOTIATION',
            'CONVERTED',
            'LOST',
        ];
    }

    /**
     * Scope for filtering by organization
     */
    public function scopeForOrganization($query, $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }
}
