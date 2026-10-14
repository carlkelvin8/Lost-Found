<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ItemReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $staffFound = $this->user()?->hasAnyRole(['admin', 'osa'])
            && (int) ($this->user()?->is_active ?? 1) === 1
            && $this->report_type === 'found';
        $studentSurrender = $staffFound && $this->boolean('surrendered_by_student');
        return [
            'submission_id' => [$this->routeIs('reports.store', 'admin.reports.store', 'session.reports.store') ? 'required' : 'nullable', 'uuid'],
            'surrendered_by_student' => ['sometimes', 'boolean', \Illuminate\Validation\Rule::prohibitedIf(!$staffFound && $this->boolean('surrendered_by_student'))],
            'finder_name' => [\Illuminate\Validation\Rule::requiredIf($studentSurrender), \Illuminate\Validation\Rule::prohibitedIf(!$studentSurrender), 'nullable', 'string', 'max:190'],
            'finder_student_id' => [\Illuminate\Validation\Rule::requiredIf($studentSurrender), \Illuminate\Validation\Rule::prohibitedIf(!$studentSurrender), 'nullable', 'string', 'max:60', 'regex:/^[\pL\pN][\pL\pN ._\/\-]*$/u'],
            'reporter_name' => [\Illuminate\Validation\Rule::excludeIf(fn () => $this->report_type !== 'found'), \Illuminate\Validation\Rule::requiredIf(fn () => $this->report_type === 'found'), 'nullable', 'string', 'max:190'],
            'report_type' => [
                'required',
                'string',
                'in:lost,found',
            ],
            'owner_user_id' => [
                'nullable',
                'integer',
                'exists:users,id',
                'min:1',
            ],
            'category_id' => [
                'nullable',
                'integer',
                'exists:categories,id',
                'min:1',
            ],
            'item_name' => [
                'nullable',
                'string',
                'max:190',
                'min:2',
                'regex:/^[a-zA-Z0-9\s\-\.\,\(\)\/]+$/',
            ],
            'item_description' => [
                'required',
                'string',
                'min:10',
                'max:5000',
            ],
            'brand_model' => [
                'nullable',
                'string',
                'max:190',
                'regex:/^[a-zA-Z0-9\s\-\.\,\/]+$/',
            ],
            'color' => [
                'nullable',
                'string',
                'max:60',
                'regex:/^[a-zA-Z\s\-\/]+$/',
            ],
            'incident_date' => [
                'nullable',
                'date',
                'before_or_equal:today',
                'after:' . now()->subYears(2)->format('Y-m-d'),
            ],
            'incident_time' => [
                'nullable',
                'regex:/^([01]?[0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?$/',
            ],
            'location_id' => [
                'nullable',
                'integer',
                'exists:locations,id',
                'min:1',
            ],
            'circumstances' => [
                'nullable',
                'string',
                'max:2000',
            ],
            'contact_override' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9\s\-\.\,\@\+\(\)]+$/',
            ],
            'photos' => [
                'nullable',
                'array',
                'max:5',
            ],
            'photos.*' => [
                'file',
                'mimes:jpg,jpeg,png,webp',
                'max:4096',
                'dimensions:min_width=100,min_height=100,max_width=4000,max_height=4000',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'finder_name.required' => 'Finder name is required for an item surrendered by a student.',
            'finder_student_id.required' => 'Finder student ID is required for an item surrendered by a student.',
            'finder_student_id.regex' => 'Enter a valid student ID using letters, numbers, spaces, dots, slashes, underscores or hyphens.',
            'surrendered_by_student.prohibited' => 'Only Admin/OSA can record student surrender details on Found reports.',
            'finder_name.prohibited' => 'Finder details apply only to an admin Found report surrendered by a student.',
            'finder_student_id.prohibited' => 'Finder details apply only to an admin Found report surrendered by a student.',
            'reporter_name.required' => "Finder's name is required for a Found report.",
            'report_type.required' => 'Please select report type (Lost or Found).',
            'report_type.in' => 'Report type must be either Lost or Found.',
            'item_name.min' => 'Item name must be at least 2 characters.',
            'item_name.regex' => 'Item name contains invalid characters.',
            'item_description.required' => 'Item description is required.',
            'item_description.min' => 'Description must be at least 10 characters.',
            'item_description.max' => 'Description cannot exceed 5000 characters.',
            'brand_model.regex' => 'Brand/model contains invalid characters.',
            'color.regex' => 'Color can only contain letters, spaces, and hyphens.',
            'incident_date.before_or_equal' => 'Incident date cannot be in the future.',
            'incident_date.after' => 'Incident date cannot be more than 2 years ago.',
            'incident_time.regex' => 'Please provide a valid time format (HH:MM).',
            'circumstances.max' => 'Circumstances cannot exceed 2000 characters.',
            'contact_override.regex' => 'Contact information contains invalid characters.',
            'photos.max' => 'You can upload a maximum of 5 photos.',
            'photos.*.mimes' => 'Photos must be JPG, JPEG, PNG, or WEBP format.',
            'photos.*.max' => 'Each photo must not exceed 4MB.',
            'photos.*.dimensions' => 'Photo dimensions must be between 100x100 and 4000x4000 pixels.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->routeIs('reports.update', 'admin.reports.update', 'session.reports.update')) {
            // Validate the stored type; a forged Lost type cannot bypass Found requirements.
            $report = \App\Models\ItemReport::find($this->route('id'));
            if ($report) $this->merge(['report_type' => $report->report_type]);
        }
        foreach (['finder_name', 'finder_student_id'] as $field) {
            if ($this->has($field) && is_string($this->input($field))) {
                $this->merge([$field => preg_replace('/^[\s\p{Z}]+|[\s\p{Z}]+$/u', '', $this->input($field))]);
            }
        }
        // Student surrender already collects the finder's name in its own field.
        if ($this->report_type === 'found' && $this->boolean('surrendered_by_student')
            && $this->user()?->hasAnyRole(['admin', 'osa'])) {
            $this->merge(['reporter_name' => $this->input('finder_name')]);
        }
        $this->merge([
            'reporter_name' => trim($this->reporter_name ?? ''),
            'item_name' => trim($this->item_name ?? ''),
            'item_description' => trim($this->item_description ?? ''),
            'brand_model' => trim($this->brand_model ?? ''),
            'color' => ucfirst(strtolower(trim($this->color ?? ''))),
            'circumstances' => trim($this->circumstances ?? ''),
            'contact_override' => trim($this->contact_override ?? ''),
        ]);
    }
}
