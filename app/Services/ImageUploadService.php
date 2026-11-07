<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

class ImageUploadService
{
    public const PROFILES = [
        'product' => [2400, 2400, 85],
        'banner' => [1920, 1080, 80],
        'category' => [800, 800, 82],
        'logo' => [800, 800, 85],
        'settings' => [1600, 1600, 85],
        'cta' => [1600, 1600, 82],
    ];

    /** Store on the public disk and return its relative path, not a URL. */
    public function store(UploadedFile $file, string $directory, string $profile, string $name = 'image'): string
    {
        $options = self::PROFILES[$profile] ?? throw new InvalidArgumentException('Unknown image profile.');
        $optimized = (new ImageOptimizer)->optimize($file->getRealPath(), ...$options);
        if ($optimized !== null) {
            $prefix = Str::limit(Str::slug($name), 80, '') ?: 'image';
            $path = trim($directory, '/').'/'.$prefix.'-'.Str::uuid().'.webp';
            if (! Storage::disk('public')->put($path, $optimized, 'public')) {
                throw new RuntimeException('Unable to save the optimized image.');
            }

            return $path;
        }

        // Preserve originals when conversion is unsupported or would increase size.
        $path = $file->storePublicly($directory, 'public');
        if (! $path) {
            throw new RuntimeException('Unable to save the uploaded image.');
        }

        return $path;
    }
}
