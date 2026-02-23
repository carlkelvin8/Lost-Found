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
}
