<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserAccountTypeTest extends TestCase
{
    use RefreshDatabase;

    private function adminUser(): User
    {
        $admin = User::factory()->create();
        $admin->roles()->firstOrCreate(['name' => 'admin']);
        return $admin;
    }

    private function osaUser(): User
    {
        $osa = User::factory()->create();
        $osa->roles()->firstOrCreate(['name' => 'osa']);
        return $osa;
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'email'                 => 'new.student@naap.edu.ph',
            'password'              => 'Secret-123!xyz',
            'password_confirmation' => 'Secret-123!xyz',
            'full_name'             => 'Maria Santos',
            'user_type'             => 'student',
            'school_id_number'      => null,
            'contact_no'            => null,
        ], $overrides);
    }

    private function userWithStudentProfile(string $email, string $name = 'Target User'): User
    {
        $user = User::factory()->create(['email' => $email]);
        $user->profile()->create([
            'full_name'  => $name,
            'user_type'  => 'student',
        ]);
        $user->roles()->firstOrCreate(['name' => 'student']);
        return $user;
    }

    public function test_admin_creating_a_student_sets_student_role_and_profile(): void
    {
        $this->actingAs($this->adminUser())
            ->post(route('users.store'), $this->payload())
            ->assertRedirect(route('users.index'));

        $user = User::where('email', 'new.student@naap.edu.ph')->first();

        $this->assertNotNull($user);
        $this->assertTrue($user->hasAnyRole(['student']));
        $this->assertSame('student', $user->profile->user_type);
        $this->assertFalse($user->hasAnyRole(['admin', 'osa']));
    }

    public function test_admin_creating_an_admin_osa_account_sets_both_privileged_roles(): void
    {
        $this->actingAs($this->adminUser())
            ->post(route('users.store'), $this->payload([
                'email'     => 'admin.osa@naap.edu.ph',
                'user_type' => 'admin_osa',
            ]))
            ->assertRedirect(route('users.index'));

        $user = User::where('email', 'admin.osa@naap.edu.ph')->first();

        $this->assertNotNull($user);
        $this->assertTrue($user->hasAnyRole(['admin']));
        $this->assertTrue($user->hasAnyRole(['osa']));
        $this->assertSame('admin', $user->profile->user_type);
    }

    public function test_editing_account_type_syncs_the_single_role(): void
    {
        $target = $this->userWithStudentProfile('target@naap.edu.ph');

        $this->actingAs($this->adminUser())
            ->post(route('users.update', $target->id), [
                'email'     => $target->email,
                'full_name' => 'Target User',
                'user_type' => 'guest',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $target->refresh();

        $this->assertTrue($target->hasAnyRole(['guest']));
        $this->assertFalse($target->hasAnyRole(['student']));
        $this->assertSame('guest', $target->profile->user_type);
    }

    public function test_osa_cannot_assign_admin_osa_access(): void
    {
        $target = $this->userWithStudentProfile('target2@naap.edu.ph');

        $this->actingAs($this->osaUser())
            ->post(route('users.update', $target->id), [
                'email'     => $target->email,
                'full_name' => 'Target User',
                'user_type' => 'admin_osa',
            ])
            ->assertForbidden();
    }
}
