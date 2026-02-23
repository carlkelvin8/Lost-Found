<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Claim extends Model
{
    protected $table = 'claims';

    protected $fillable = [
        'report_id',
        'claimant_user_id',
        'proof_text',
        'status',
        'reviewed_by_user_id',
        'reviewed_at'
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function claimant()
    {
        return $this->belongsTo(User::class, 'claimant_user_id');
    }

    public function report()
    {
        return $this->belongsTo(ItemReport::class, 'report_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }

    public function documents()
    {
        return $this->hasMany(ClaimDocument::class);
    }
}
