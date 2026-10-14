<?php

namespace App\Models;

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
        'reporter_name',
        'surrendered_by_student',
        'finder_name',
        'finder_student_id',
        'submission_id',
        'status',
        'locked_at',
        'locked_by_user_id',
        'matched_report_id',
        'matched_score',
        'ai_analysis',
    ];

    protected $casts = [
        'surrendered_by_student' => 'boolean',
        'ai_analysis' => 'array',
        'locked_at' => 'datetime',
    ];

    /**
     * A report is locked after submission, when staff locks it manually, or
     * when its status is matched or terminal. Matched and terminal reports stay
     * locked even if their manual lock is cleared.
     */
    public function isLocked(): bool
    {
        if ($this->locked_at !== null) {
            return true;
        }

        return in_array((string) $this->status, ['matched', 'claimed', 'returned', 'archived'], true);
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_user_id');
    }

    /** Resolve recorded finder details, including legacy self-reported Found items. */
    public function finderIdentity(): array
    {
        $name = trim((string) $this->finder_name);
        $studentId = trim((string) $this->finder_student_id);
        if ($name !== '') {
            return ['name' => $name, 'student_id' => $studentId !== '' ? $studentId : null];
        }

        $overrideName = trim((string) $this->reporter_name);
        if ($overrideName !== '') {
            // A separately named finder must not inherit the submitting account's ID.
            return ['name' => $overrideName, 'student_id' => $studentId !== '' ? $studentId : null];
        }

        $reporter = $this->reporter;
        if ($reporter && !$reporter->hasAnyRole(['admin', 'osa'])) {
            $name = trim((string) ($reporter->profile?->full_name ?: $reporter->name));
            if ($studentId === '') $studentId = trim((string) $reporter->profile?->school_id_number);
            return ['name' => $name !== '' ? $name : null, 'student_id' => $studentId !== '' ? $studentId : null];
        }

        // An admin may submit on someone else's behalf; never guess their finder identity.
        return ['name' => null, 'student_id' => $studentId !== '' ? $studentId : null];
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
