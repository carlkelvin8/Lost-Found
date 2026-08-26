<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClaimRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'report_id' => [
                'required',
                'integer',
                'exists:item_reports,id',
                'min:1',
            ],
            'proof_text' => [
                'required',
                'string',
                'min:20',
                'max:2000',
            ],
            'description' => [
                'nullable',
                'string',
                'min:20',
                'max:2000',
            ],
            'proof_description' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'contact_info' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9\s\-\.\,\@\+\(\)]+$/',
            ],
            'documents' => [
                'nullable',
                'array',
                'max:3',
            ],
            'documents.*' => [
                'file',
                'mimes:jpg,jpeg,png,pdf,webp',
                'max:5120',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'report_id.required' => 'Report ID is required.',
            'report_id.exists' => 'The selected report does not exist.',
            'proof_text.required' => 'Proof description is required.',
            'proof_text.min' => 'Proof description must be at least 20 characters to provide sufficient detail.',
            'proof_text.max' => 'Proof description cannot exceed 2000 characters.',
            'description.min' => 'Description must be at least 20 characters.',
            'description.max' => 'Description cannot exceed 2000 characters.',
            'contact_info.regex' => 'Contact information contains invalid characters.',
            'documents.max' => 'You can upload a maximum of 3 documents.',
            'documents.*.mimes' => 'Documents must be JPG, JPEG, PNG, PDF, or WEBP format.',
            'documents.*.max' => 'Each document must not exceed 5MB.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'proof_text' => trim($this->proof_text ?? ''),
            'description' => trim($this->description ?? $this->proof_text ?? ''),
            'proof_description' => trim($this->proof_description ?? ''),
            'contact_info' => trim($this->contact_info ?? ''),
        ]);
    }
}
