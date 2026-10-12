<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'full_name'              => 'Juan Dela Cruz',
            'email'                  => 'guest.2026.test@gmail.com',
            'password'               => 'Yp8$kVz#317QrBx',
            'password_confirmation'  => 'Yp8$kVz#317QrBx',
            'user_type'              => 'guest',
            'contact_no'             => '09171234567',
            'address'                => 'Manila',
            'department_name'        => null,
            'school_id_number'       => null,
        ], $overrides);
    }

    public function test_a_guest_can_register_and_receive_the_guest_role(): void
    {
        Mail::fake();

        $this->post(route('register.post'), $this->payload())
            ->assertRedirect(route('verification.notice'));

        $user = User::where('email', 'guest.2026.test@gmail.com')->first();

        $this->assertNotNull($user);
        $this->assertTrue($user->hasAnyRole(['guest']));
        $this->assertSame('guest', $user->profile->user_type);
    }

    public function test_each_user_type_registers_with_its_own_role_and_profile_type(): void
    {
        foreach (['faculty', 'staff'] as $type) {
            Mail::fake();

            $email = "{$type}.2026.test@gmail.com";

            $this->post(route('register.post'), $this->payload([
                'user_type' => $type,
                'email'     => $email,
            ]))->assertRedirect(route('verification.notice'));

            $user = User::where('email', $email)->first();

            $this->assertNotNull($user);
            $this->assertTrue($user->hasAnyRole([$type]));
            $this->assertSame($type, $user->profile->user_type);
        }
    }

    public function test_a_student_must_provide_school_id_and_department(): void
    {
        $this->post(route('register.post'), $this->payload(['user_type' => 'student']))
            ->assertSessionHasErrors(['school_id_number', 'department_name']);

        $this->assertSame(0, User::count());
    }

    public function test_a_student_can_register_after_departments_are_auto_seeded(): void
    {
        Mail::fake();

        // Simulate a live database that never got the departments seeded:
        // visiting the form must restore the defaults so the student
        // dropdown is never empty.
        Department::query()->delete();
        $this->assertSame(0, Department::count());

        $this->get(route('register'))->assertOk();

        $this->assertSame(count(Department::DEFAULT_NAMES), Department::count());

        $this->post(route('register.post'), $this->payload([
            'user_type'        => 'student',
            'email'            => 'student.2026.test@gmail.com',
            'school_id_number' => '2024-12345',
            'department_name'  => 'ICS',
        ]))->assertRedirect(route('verification.notice'));

        $user = User::where('email', 'student.2026.test@gmail.com')->first();

        $this->assertNotNull($user);
        $this->assertSame('student', $user->profile->user_type);
        $this->assertNotNull($user->profile->department_id);
    }

    public function test_faculty_staff_and_guest_are_not_forced_into_student_fields(): void
    {
        foreach (['faculty', 'staff', 'guest'] as $type) {
            Mail::fake();

            $this->post(route('register.post'), $this->payload([
                'user_type' => $type,
                'email'     => "{$type}.2026.test@gmail.com",
            ]))->assertRedirect(route('verification.notice'));

            $user = User::where('email', "{$type}.2026.test@gmail.com")->first();
            $this->assertNotNull($user);
            $this->assertEmpty($user->profile->school_id_number);

            User::where('email', "{$type}.2026.test@gmail.com")->delete();
        }
    }
}