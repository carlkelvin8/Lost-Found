<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateAdminCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_verified_active_admin_with_the_admin_role(): void
    {
        $this->artisan('admin:create', [
            '--email'    => 'boss@naap.edu.ph',
            '--password' => 'Secret@12345',
            '--name'     => 'Boss Admin',
        ])->assertSuccessful();

        $user = User::where('email', 'boss@naap.edu.ph')->first();

        $this->assertNotNull($user);
        $this->assertTrue($user->hasAnyRole(['admin']));
        $this->assertNotNull($user->email_verified_at);
        $this->assertSame(1, (int) $user->is_active);
        $this->assertSame('Boss Admin', $user->profile->full_name);
    }

    public function test_it_updates_an_existing_account_without_duplicating_the_role(): void
    {
        $this->artisan('admin:create', [
            '--email'    => 'boss@naap.edu.ph',
            '--password' => 'Secret@12345',
            '--name'     => 'Boss Admin',
        ])->assertSuccessful();

        $this->artisan('admin:create', [
            '--email'    => 'boss@naap.edu.ph',
            '--password' => 'Changed@12345',
            '--name'     => 'Renamed Admin',
        ])->assertSuccessful();

        $user = User::where('email', 'boss@naap.edu.ph')->first();

        $this->assertSame(1, User::where('email', 'boss@naap.edu.ph')->count());
        $this->assertSame(1, $user->roles()->count());
        $this->assertSame('Renamed Admin', $user->profile->full_name);
    }

    public function test_it_rejects_a_password_shorter_than_eight_characters(): void
    {
        $this->artisan('admin:create', [
            '--email'    => 'boss@naap.edu.ph',
            '--password' => 'short',
            '--name'     => 'Boss Admin',
        ])->assertFailed();

        $this->assertSame(0, User::where('email', 'boss@naap.edu.ph')->count());
    }
}
