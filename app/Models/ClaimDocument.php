<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClaimDocument extends Model
{
    protected $table = 'claim_documents';

    const UPDATED_AT = null;

    protected $fillable = [
        'claim_id',
        'file_url',
        'file_type',
        'file_hash_sha256'
    ];
}
