<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportMatch extends Model
{
    protected $table = 'report_matches';

    protected $fillable = [
        'lost_report_id',
        'found_report_id',
        'score',
        'method',
        'status'
    ];

    public function lostReport()
    {
        return $this->belongsTo(ItemReport::class, 'lost_report_id');
    }

    public function foundReport()
    {
        return $this->belongsTo(ItemReport::class, 'found_report_id');
    }
}
