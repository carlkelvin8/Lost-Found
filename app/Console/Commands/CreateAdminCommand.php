<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CreateAdminCommand extends Command
{
    protected $signature = 'admin:create
        {--email= : Admin email address (defaults to admin@naap.edu.ph)}
        {--password= : Admin password, min 8 characters (omit to be prompted or auto-generate)}
        {--name= : Admin display name}';

    protected $description = 'Create or update an administrator account (email pre-verified, active).';

    public function handle(): int
    {
        $email = strtolower(trim((string) ($this->option('email') ?: $this->ask('Admin email', 'admin@naap.edu.ph'))));

        $validator = Validator::make(
            ['email' => $email],
            ['email' => ['required', 'email', 'max:190']]
        );

        if ($validator->fails()) {
            $this->error($validator->errors()->first('email'));

            return self::FAILURE;
        }

        $password = $this->option('password');
        $generated = false;

        if (!$password) {
            $password = $this->secret('Admin password (leave blank to auto-generate)');

            if ($password === null || $password === '') {
                $password = Str::password(16);
                $generated = true;
            }
        }

        if (strlen((string) $password) < 8) {
            $this->error('Password must be at least 8 characters.');

            return self::FAILURE;
        }

        $name = $this->option('name') ?: $this->ask('Admin full name', 'Administrator');

        $role = Role::firstOrCreate(
            ['name' => 'admin'],
            ['description' => 'System Administrator']
        );

        $existing = User::where('email', $email)->exists();

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'password'          => Hash::make($password),
                'is_active'         => 1,
                'email_verified_at' => now(),
            ]
        );

        UserProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'full_name' => $name,
                'user_type' => 'staff',
            ]
        );

        $user->roles()->syncWithoutDetaching([$role->id]);

        $this->info('Admin account ' . ($existing ? 'updated' : 'created') . ' successfully.');
        $this->line('Email: ' . $email);

        if ($generated) {
            $this->line('Generated password: ' . $password);
        } else {
            $this->line('Password: ' . ($this->option('password') ? '(from --password option)' : '(as entered)'));
        }

        $this->warn('Change this password after first login.');

        return self::SUCCESS;
    }
}
