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

        $fullPath = 'public/' . $path;

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
