<?php

namespace App\Models;

use App\Services\ClaimEligibilityService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemReport extends Model
{
    use HasFactory;

    protected $table = 'item_reports';

    protected $fillable = [
        'report_type',
        'reporter_user_id',
        'owner_user_id',
        'category_id',
        'item_name',
        'item_description',
        'brand_model',
        'color',
        'incident_date',
        'incident_time',
        'location_id',
        'circumstances',
        'contact_override',
        'status',
        'locked_at',
        'locked_by_user_id',
        'matched_report_id',
        'matched_score',
        'ai_analysis',
    ];

    protected $casts = [
        'ai_analysis' => 'array',
        'locked_at' => 'datetime',
    ];

    /**
     * A report is locked when a staff member locked it manually, or when its
     * status is terminal (returned / archived). Locked reports cannot be
     * edited by anyone — including staff — until they are unlocked.
     */
    public function isLocked(): bool
    {
        if ($this->locked_at !== null) {
            return true;
        }

        return in_array((string) $this->status, ClaimEligibilityService::LOCKED_STATUSES, true);
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_user_id');
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function photos()
    {
        return $this->hasMany(ReportPhoto::class, 'report_id');
    }

    public function claims()
    {
        return $this->hasMany(Claim::class, 'report_id');
    }

    public function lostMatches()
    {
        return $this->hasMany(ReportMatch::class, 'lost_report_id');
    }

    public function foundMatches()
    {
        return $this->hasMany(ReportMatch::class, 'found_report_id');
    }

    public function statusHistory()
    {
        return $this->hasMany(ReportStatusHistory::class, 'report_id');
    }
}
