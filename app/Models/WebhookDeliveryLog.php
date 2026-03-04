<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebhookDeliveryLog extends Model
{
    use HasFactory;

    /**
     * The table only has 'created_at', not 'updated_at'.
     */
    public $timestamps = false;

    protected $fillable = [
        'webhook_id',
        'event',
        'resource_type',
        'resource_id',
        'status_code',
        'error_message',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function webhook(): BelongsTo
    {
        return $this->belongsTo(Webhook::class);
    }
}
