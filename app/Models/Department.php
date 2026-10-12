<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    /**
     * Departments the application ships with. Registration expects at least
     * these to exist so students can always pick one on a fresh database.
     */
    public const DEFAULT_NAMES = ['ICS', 'ILAS', 'INET'];

    protected $table = 'departments';

    protected $fillable = ['name'];

    public function users()
    {
        return $this->hasMany(UserProfile::class, 'department_id');
    }
}
