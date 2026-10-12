<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'string',
                'email:rfc,dns',
                'max:190',
                'unique:users,email',
                'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
            ],
            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(8)
                    ->mixedCase()
                    ->letters()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(),
            ],
            'full_name' => [
                'required',
                'string',
                'min:2',
                'max:190',
                'regex:/^[a-zA-Z\s\-\.\']+$/',
            ],
            // Four self-service account types: Student, Faculty, Staff, Guest
            'user_type' => [
                'required',
                'string',
                'in:student,faculty,staff,guest',
            ],
            // Only students must carry a department / school ID — Faculty,
            // Staff and Guest never have to supply them.
            'department_name' => [
                'required_if:user_type,student',
                'nullable',
                'string',
                Rule::exists('departments', 'name'),
            ],
            'school_id_number' => [
                'required_if:user_type,student',
                'nullable',
                'string',
                'max:60',
                'regex:/^[A-Z0-9\-]+$/i',
            ],
            'contact_no' => [
                'nullable',
                'string',
                'max:40',
                'regex:/^[\+]?[(]?[0-9]{1,4}[)]?[-\s\.]?[(]?[0-9]{1,4}[)]?[-\s\.]?[0-9]{1,9}$/',
            ],
            'address' => [
                'nullable',
                'string',
                'max:255',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Email address is required.',
            'email.email' => 'Please provide a valid email address.',
            'email.unique' => 'This email is already registered.',
            'email.regex' => 'Email format is invalid.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
            'full_name.required' => 'Full name is required.',
            'full_name.min' => 'Full name must be at least 2 characters.',
            'full_name.regex' => 'Full name can only contain letters, spaces, hyphens, dots, and apostrophes.',
            'school_id_number.required_if' => 'School ID is required when registering as a Student.',
            'school_id_number.regex' => 'School ID must contain only letters, numbers, and hyphens.',
            'department_name.required_if' => 'Department is required when registering as a Student.',
            'contact_no.regex' => 'Please provide a valid phone number format.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $userType = strtolower(trim((string) ($this->user_type ?? '')));

        // Legacy "visitor" accounts were renamed to "guest"
        if ($userType === 'visitor') {
            $userType = 'guest';
        }

        $this->merge([
            'email' => strtolower(trim($this->email ?? '')),
            'full_name' => trim($this->full_name ?? ''),
            'user_type' => $userType,
            'school_id_number' => strtoupper(trim($this->school_id_number ?? '')),
            'contact_no' => preg_replace('/[^0-9\+\-\(\)\s]/', '', $this->contact_no ?? ''),
        ]);
    }
}
