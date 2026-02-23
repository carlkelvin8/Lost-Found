<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'notifications';

    const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'title',
        'body',
        'notif_type',
        'read_at',
        'data_json',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];
}
