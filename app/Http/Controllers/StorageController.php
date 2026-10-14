<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;

class StorageController extends WebBaseController
{
    /**
     * Serve files from storage/app/public.
     * Requires authenticated user to prevent public file enumeration.
     */
    public function serve($path)
    {
        if (!$this->user()) {
            abort(401);
        }

        $path = str_replace('\\', '/', $path);
        if (str_contains($path, '..') || str_contains($path, "\0")) {
            abort(403);
        }

        $disk = Storage::disk(app(\App\Services\UploadedImageStorage::class)->diskName());
        if (!$disk->exists($path)) {
            abort(404);
        }

        if (config('filesystems.disks.' . app(\App\Services\UploadedImageStorage::class)->diskName() . '.driver') === 's3') {
            return redirect()->away($disk->temporaryUrl($path, now()->addHour()));
        }

        $file = realpath($disk->path($path));
        $base = realpath($disk->path(''));
        if (!$file || !$base || !str_starts_with($file, $base . DIRECTORY_SEPARATOR) || !is_file($file)) {
            abort(403);
        }

        $mimeType = mime_content_type($file) ?: 'application/octet-stream';

        return response()->file($file, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }
}
