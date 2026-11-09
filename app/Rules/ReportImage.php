<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ReportImage implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value instanceof \Illuminate\Http\UploadedFile || ! $value->isValid()) {
            $fail('The photo could not be uploaded. Please select it again.');

            return;
        }
        $info = @getimagesize($value->getPathname());
        if (! $info || ! in_array($info['mime'], ['image/jpeg', 'image/png', 'image/webp'], true)) {
            $fail('Use a valid JPEG, PNG or WebP photo. For HEIC/HEIF, use a browser that converts it or export as JPEG.');

            return;
        }
        if ($info[0] < 100 || $info[1] < 100 || config('reports.photo_max_pixels') < $info[0] * $info[1]) {
            $fail('Photo must be at least 100 × 100 pixels and at most 20 megapixels. Please resize it and try again.');
        }
        $memoryLimit = ini_parse_quantity(ini_get('memory_limit'));
        if ($memoryLimit > 0 && $memoryLimit < $info[0] * $info[1] * 9 + 16 * 1024 * 1024 + memory_get_usage(true)) {
            $fail('This photo needs more memory than the server allows. Optimize it in the report form or resize to 1600 pixels.');
        }
    }

    public static function rules(): array
    {
        return ['bail', 'file', 'max:'.config('reports.photo_max_kb'), new self];
    }
}
