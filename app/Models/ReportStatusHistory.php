<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportStatusHistory extends Model
{
    protected $table = 'report_status_history';

    // WALANG created_at / updated_at sa table
    public $timestamps = false;

    protected $fillable = [
        'report_id',
        'old_status',
        'new_status',
        'changed_by_user_id',
        'note',
        'changed_at',
    ];
}
