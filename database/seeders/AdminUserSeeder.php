<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\UserProfile;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        $role = Role::firstOrCreate(['name' => 'admin'], ['description' => 'Administrator']);

        $user = User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'password' => Hash::make('password123'),
                'is_active' => 1,
                'email_verified_at' => now(),
            ]
        );

        // Ensure password is correct if user already existed
        $user->password = Hash::make('password123');
        $user->save();

        UserProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'full_name' => 'System Admin',
                'user_type' => 'staff',
                'school_id_number' => 'ADMIN001',
                'contact_no' => '0000000000'
            ]
        );

        if (!$user->roles->contains($role->id)) {
            $user->roles()->attach($role);
        }

        $this->command->info('Admin user created: admin@admin.com / password123');
    }
}
