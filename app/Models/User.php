<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'email',
        'password',              // ✅ FIXED
        'is_active',
        'email_verified_at',
        'last_login_at',
    ];

    protected $hidden = [
        'password',              // ✅ FIXED
        'remember_token',
    ];

    /**
     * ✅ Explicitly tell Laravel which column stores the password
     */
    public function getAuthPassword()
    {
        return $this->password;
    }

    public function profile()
    {
        return $this->hasOne(UserProfile::class, 'user_id');
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles');
    }

    public function reports()
    {
        return $this->hasMany(ItemReport::class, 'reporter_user_id');
    }
}
