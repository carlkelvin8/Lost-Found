<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserProfile;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestUserSeeder extends Seeder
{
    public function run(): void
    {
        // Create test student user
        $student = User::firstOrCreate(
            ['email' => 'student@naap.edu.ph'],
            [
                'password' => Hash::make('Student@12345'),
                'is_active' => 1,
                'email_verified_at' => now(),
                'last_login_at' => null,
            ]
        );

        // Create profile if not exists
        if (!$student->profile) {
            UserProfile::create([
                'user_id' => $student->id,
                'full_name' => 'Juan Dela Cruz',
                'school_id_number' => 'STU-2024-001',
                'department_id' => 'ICS',
                'contact_no' => '09171234567',
                'user_type' => 'student',
                'address' => 'Manila, Philippines',
            ]);
        }

        // Assign student role
        $studentRole = Role::firstOrCreate(['name' => 'student']);
        $student->roles()->syncWithoutDetaching([$studentRole->id]);

        $this->command->info('Test student account created!');
        $this->command->info('Email: student@naap.edu.ph');
        $this->command->info('Password: Student@12345');
    }
}
