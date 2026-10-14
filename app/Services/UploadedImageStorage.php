<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class UploadedImageStorage
{
    public function diskName(): string
    {
        $default = config('filesystems.default');
        return config('filesystems.uploads') ?: ($default === 'local' ? 'public' : $default);
    }

    public function key(?string $path): ?string
    {
        if (!$path) return null;
        if (preg_match('~^https?://~i', $path)) {
            $probe = Storage::disk($this->diskName())->url('__image_base__');
            $base = substr($probe, 0, strrpos($probe, '/') + 1);
            if (!str_starts_with($path, $base)) return null;
            $path = rawurldecode(explode('?', substr($path, strlen($base)), 2)[0]);
        }
        $path = ltrim(str_replace('\\', '/', $path), '/');
        if (str_contains($path, '..') || str_contains($path, "\0")) return null;
        return str_starts_with($path, 'storage/') ? substr($path, 8) : $path;
    }

    public function url(?string $path): string
    {
        if (!$path) return '';
        $key = $this->key($path);
        if ($key === null) return preg_match('~^https?://~i', $path) ? $path : '';
        $name = $this->diskName();
        $disk = Storage::disk($name);
        if (config("filesystems.disks.{$name}.driver") === 's3') {
            // Sign at display time so private bucket links are never persisted expired.
            return $disk->temporaryUrl($key, now()->addHour());
        }
        if ($disk->exists($key)) return route('storage.serve', ['path' => $key]);
        if (is_file(public_path($key))) return asset($key);
        return asset(ltrim($path, '/'));
    }

    public function store($file, string $directory): string
    {
        $path = Storage::disk($this->diskName())->putFile($directory, $file);
        if (!$path) throw new \RuntimeException('Image upload failed. Please try again.');
        return $path;
    }

    public function put(string $key, string $contents): string
    {
        if (!Storage::disk($this->diskName())->put($key, $contents)) {
            throw new \RuntimeException('Image upload failed. Please try again.');
        }
        return $key;
    }

    public function read(?string $path): ?string
    {
        $key = $this->key($path);
        if ($key === null) return null;
        $disk = Storage::disk($this->diskName());
        if ($disk->exists($key)) return $disk->get($key);
        if ($this->diskName() !== 'public' && Storage::disk('public')->exists($key)) {
            return Storage::disk('public')->get($key);
        }
        return is_file(public_path($key)) ? file_get_contents(public_path($key)) : null;
    }

    public function delete(?string $path): void
    {
        $key = $this->key($path);
        if (!$key || !preg_match('~^(avatars|report_photos)/~', $key)) return;
        $disk = Storage::disk($this->diskName());
        if ($disk->exists($key)) {
            $disk->delete($key);
            return;
        }
        if ($this->diskName() !== 'public' && Storage::disk('public')->exists($key)) {
            Storage::disk('public')->delete($key);
            return;
        }
        $file = realpath(public_path($key));
        $base = realpath(public_path());
        if ($file && $base && str_starts_with($file, $base . DIRECTORY_SEPARATOR) && is_file($file)) {
            unlink($file);
        }
    }
}
