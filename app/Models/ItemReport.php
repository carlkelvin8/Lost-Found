<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemReport extends Model
{
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
        'matched_report_id',
        'matched_score',
        'ai_analysis',
    ];

    protected $casts = [
        'ai_analysis' => 'array',
    ];

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
}
