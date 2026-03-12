<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;

class StorageController extends Controller
{
    /**
     * Serve files from storage/app/public
     */
    public function serve($path)
    {
        $fullPath = 'public/' . $path;

        if (!Storage::exists($fullPath)) {
            abort(404, 'File not found: ' . $fullPath);
        }

        $file = Storage::path($fullPath);
        
        if (!file_exists($file)) {
            abort(404, 'File does not exist: ' . $file);
        }

        $mimeType = mime_content_type($file) ?: 'application/octet-stream';

        return response()->file($file, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
