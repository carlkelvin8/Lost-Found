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

        $fullPath = 'public/' . $path;

        $resolved = realpath(storage_path('app/' . $fullPath));
        $base = realpath(storage_path('app/public'));
        if (!$resolved || !str_starts_with($resolved, $base)) {
            abort(403);
        }

        if (!Storage::exists($fullPath)) {
            abort(404);
        }

        $file = Storage::path($fullPath);

        if (!file_exists($file)) {
            abort(404);
        }

        $mimeType = mime_content_type($file) ?: 'application/octet-stream';

        return response()->file($file, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
