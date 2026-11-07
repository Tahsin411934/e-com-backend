<?php

namespace Modules\Frontend\Services;

class BannerImageOptimizer
{
    /** Return a smaller static WebP, or null to preserve the original upload. */
    public function optimize(string $path): ?string
    {
        if (! function_exists('imagecreatefromstring') || ! function_exists('imagewebp')) {
            return null;
        }

        $size = @getimagesize($path);
        // Preserve GIF/SVG/animated WebP and avoid unbounded decoder memory usage.
        if (! $size || ! in_array($size[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG], true)
            || $size[0] * $size[1] > 16000000) {
            return null;
        }

        $original = file_get_contents($path);
        $source = @imagecreatefromstring($original);
        if (! $source) {
            return null;
        }

        $target = null;
        $stream = null;

        try {
            // Respect camera orientation before removing EXIF metadata.
            if ($size[2] === IMAGETYPE_JPEG && function_exists('exif_read_data')) {
                $orientation = (@exif_read_data($path)['Orientation'] ?? 1);
                if ($orientation !== 1) {
                    // Keep uncommon EXIF orientations unchanged rather than rotate incorrectly.
                    return null;
                }
            }

            $scale = min(1, 1920 / $size[0], 1080 / $size[1]);
            $width = max(1, (int) round($size[0] * $scale));
            $height = max(1, (int) round($size[1] * $scale));
            $target = imagecreatetruecolor($width, $height);
            imagealphablending($target, false);
            imagesavealpha($target, true);
            imagefill($target, 0, 0, imagecolorallocatealpha($target, 0, 0, 0, 127));
            imagecopyresampled($target, $source, 0, 0, 0, 0, $width, $height, $size[0], $size[1]);

            $stream = fopen('php://temp', 'w+b');
            if (! imagewebp($target, $stream, 80)) {
                return null;
            }
            rewind($stream);
            $encoded = stream_get_contents($stream);

            return $encoded !== false && strlen($encoded) > 0 && strlen($encoded) < strlen($original)
                ? $encoded : null;
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
            if ($target) {
                imagedestroy($target);
            }
            imagedestroy($source);
        }
    }
}
