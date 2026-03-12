<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserProfile;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user
        $admin = User::create([
            'email' => 'admin@naap.edu.ph',
            'password' => Hash::make('Admin@12345'),
            'is_active' => 1,
            'email_verified_at' => now(),
            'last_login_at' => null,
        ]);

        // Create admin profile
        UserProfile::create([
            'user_id' => $admin->id,
            'full_name' => 'Administrator',
            'school_id_number' => 'ADMIN-001',
            'department_id' => null,
            'contact_no' => null,
        ]);

        // Assign admin role
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $admin->roles()->attach($adminRole->id);
        }

        $this->command->info('Admin account created successfully!');
        $this->command->info('Email: admin@naap.edu.ph');
        $this->command->info('Password: Admin@12345');
    }
}
