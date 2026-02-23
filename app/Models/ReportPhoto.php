<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportPhoto extends Model
{
    protected $table = 'report_photos';

    public $timestamps = false; // ✅ IMPORTANT FIX

    protected $fillable = [
        'report_id',
        'photo_url',
        'caption',
        'created_at',
    ];
}
