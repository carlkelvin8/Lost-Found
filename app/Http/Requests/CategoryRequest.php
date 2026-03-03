<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->roles()->whereIn('name', ['admin', 'osa'])->exists();
    }

    public function rules(): array
    {
        $categoryId = $this->route('id');

        return [
            'name' => [
                'required',
                'string',
                'min:2',
                'max:100',
                'regex:/^[a-zA-Z0-9\s\-\&\/]+$/',
                Rule::unique('categories', 'name')->ignore($categoryId),
            ],
            'description' => [
                'nullable',
                'string',
                'max:500',
            ],
            'icon' => [
                'nullable',
                'string',
                'max:50',
                'regex:/^[a-z0-9\-]+$/',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Category name is required.',
            'name.min' => 'Category name must be at least 2 characters.',
            'name.max' => 'Category name cannot exceed 100 characters.',
            'name.regex' => 'Category name can only contain letters, numbers, spaces, hyphens, ampersands, and slashes.',
            'name.unique' => 'This category name already exists.',
            'description.max' => 'Description cannot exceed 500 characters.',
            'icon.regex' => 'Icon name must be lowercase with hyphens only.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim($this->name ?? ''),
            'description' => trim($this->description ?? ''),
            'icon' => strtolower(trim($this->icon ?? '')),
        ]);
    }
}
