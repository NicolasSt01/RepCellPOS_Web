<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class R2StorageService
{
    protected const DISK = 'r2';

    protected const ALLOWED_MIME_TYPES = [
        'image/jpeg',
        'image/png',
        'image/webp',
        'image/gif',
    ];

    protected const MAX_SIZE = 5 * 1024 * 1024; // 5MB

    public function upload(UploadedFile $file, string $prefix = 'uploads'): string
    {
        $this->validate($file);

        $extension = $file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'jpg';
        $filename = Str::uuid() . '.' . $extension;
        $path = rtrim($prefix, '/') . '/' . date('Y/m') . '/' . $filename;

        Storage::disk(self::DISK)->put($path, $file->getContent(), [
            'visibility' => 'public',
            'ContentType' => $file->getMimeType(),
        ]);

        return $path;
    }

    public function uploadFromBase64(string $base64, string $prefix = 'uploads', ?string $filename = null): string
    {
        $data = explode(',', $base64, 2);
        if (count($data) !== 2) {
            throw new \InvalidArgumentException('Invalid base64 string');
        }

        $decoded = base64_decode($data[1], true);
        if ($decoded === false) {
            throw new \InvalidArgumentException('Failed to decode base64');
        }

        if (strlen($decoded) > self::MAX_SIZE) {
            throw new \InvalidArgumentException('File too large (max ' . (self::MAX_SIZE / 1024 / 1024) . 'MB)');
        }

        $mimeType = $this->detectMimeTypeFromBase64($data[0]);
        if (!in_array($mimeType, self::ALLOWED_MIME_TYPES)) {
            throw new \InvalidArgumentException('Unsupported file type');
        }

        $extension = match ($mimeType) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
            default => 'jpg',
        };

        $filename = $filename ?: Str::uuid() . '.' . $extension;
        $path = rtrim($prefix, '/') . '/' . date('Y/m') . '/' . $filename;

        Storage::disk(self::DISK)->put($path, $decoded, [
            'visibility' => 'public',
            'ContentType' => $mimeType,
        ]);

        return $path;
    }

    public function delete(string $path): bool
    {
        return Storage::disk(self::DISK)->delete($path);
    }

    public function exists(string $path): bool
    {
        return Storage::disk(self::DISK)->exists($path);
    }

    public function getUrl(string $path): string
    {
        return route('r2.serve', ['path' => $path]);
    }

    public function getTempUrl(string $path, int $minutes = 60): string
    {
        return $this->getUrl($path);
    }

    protected function validate(UploadedFile $file): void
    {
        if (!in_array($file->getMimeType(), self::ALLOWED_MIME_TYPES)) {
            throw new \InvalidArgumentException('Unsupported file type: ' . $file->getMimeType());
        }

        if ($file->getSize() > self::MAX_SIZE) {
            throw new \InvalidArgumentException('File too large (max ' . (self::MAX_SIZE / 1024 / 1024) . 'MB)');
        }
    }

    protected function detectMimeTypeFromBase64(string $header): string
    {
        if (preg_match('/^data:(image\/\w+);base64$/', $header, $matches)) {
            return $matches[1];
        }
        return 'image/jpeg';
    }
}
