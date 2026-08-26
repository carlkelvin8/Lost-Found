<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    protected $table = 'activity_logs';

    const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'causer_id',
        'event',
        'subject_type',
        'subject_id',
        'action',
        'description',
        'entity_type',
        'entity_id',
        'ip_address',
        'user_agent',
        'meta_json',
        'properties',
    ];

    /**
     * The user who performed the action.
     */
    public function causer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'causer_id');
    }

    /**
     * The subject model (polymorphic-style, manually implemented).
     */
    public function subject(): BelongsTo
    {
        if (!$this->subject_type || !$this->subject_id) {
            return $this->belongsTo(User::class, 'subject_id');
        }

        return $this->belongsTo($this->subject_type, 'subject_id');
    }

    /**
     * Accessor for properties stored as meta_json.
     * Supports both 'properties' and 'meta_json' column usage.
     */
    public function getPropertiesAttribute(): array
    {
        $raw = $this->attributes['properties'] ?? $this->meta_json ?? null;
        if (is_array($raw)) {
            return $raw;
        }
        return json_decode((string) $raw, true) ?? [];
    }

    /**
     * Setter for properties – stores as meta_json.
     */
    public function setPropertiesAttribute($value): void
    {
        $this->attributes['properties'] = is_string($value) ? $value : json_encode($value);
        $this->meta_json = $this->attributes['properties'];
    }
}
