<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->roles()->whereIn('name', ['admin', 'osa'])->exists();
    }

    public function rules(): array
    {
        $locationId = $this->route('id');

        return [
            'name' => [
                'required',
                'string',
                'min:2',
                'max:190',
                'regex:/^[a-zA-Z0-9\s\-\.\,\(\)\/]+$/',
                Rule::unique('locations', 'name')->ignore($locationId),
            ],
            'building' => [
                'nullable',
                'string',
                'max:100',
                'regex:/^[a-zA-Z0-9\s\-\.]+$/',
            ],
            'floor' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^[a-zA-Z0-9\s\-]+$/',
            ],
            'details' => [
                'nullable',
                'string',
                'max:255',
            ],
            'latitude' => [
                'nullable',
                'numeric',
                'between:-90,90',
            ],
            'longitude' => [
                'nullable',
                'numeric',
                'between:-180,180',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Location name is required.',
            'name.min' => 'Location name must be at least 2 characters.',
            'name.max' => 'Location name cannot exceed 190 characters.',
            'name.regex' => 'Location name contains invalid characters.',
            'name.unique' => 'This location name already exists.',
            'building.regex' => 'Building name contains invalid characters.',
            'floor.regex' => 'Floor contains invalid characters.',
            'details.max' => 'Details cannot exceed 255 characters.',
            'latitude.between' => 'Latitude must be between -90 and 90.',
            'longitude.between' => 'Longitude must be between -180 and 180.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim($this->name ?? ''),
            'building' => trim($this->building ?? ''),
            'floor' => trim($this->floor ?? ''),
            'details' => trim($this->details ?? $this->description ?? ''),
        ]);
    }
}
