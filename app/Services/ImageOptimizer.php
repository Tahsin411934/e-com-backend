<?php

namespace App\Services;

class ImageOptimizer
{
    /** Return a smaller static WebP, or null to preserve the original upload. */
    public function optimize(string $path, int $maxWidth = 1920, int $maxHeight = 1080, int $quality = 80): ?string
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
        // APNG has an animation-control chunk before image data; do not flatten it.
        if ($size[2] === IMAGETYPE_PNG) {
            $offset = 8;
            while ($offset + 12 <= strlen($original)) {
                $length = unpack('N', substr($original, $offset, 4))[1];
                $chunk = substr($original, $offset + 4, 4);
                if ($chunk === 'acTL') {
                    return null;
                }
                if ($chunk === 'IDAT' || $chunk === 'IEND') {
                    break;
                }
                $offset += 12 + $length;
            }
        }
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

            $scale = min(1, $maxWidth / $size[0], $maxHeight / $size[1]);
            $width = max(1, (int) round($size[0] * $scale));
            $height = max(1, (int) round($size[1] * $scale));
            $target = imagecreatetruecolor($width, $height);
            imagealphablending($target, false);
            imagesavealpha($target, true);
            imagefill($target, 0, 0, imagecolorallocatealpha($target, 0, 0, 0, 127));
            imagecopyresampled($target, $source, 0, 0, 0, 0, $width, $height, $size[0], $size[1]);

            $stream = fopen('php://temp', 'w+b');
            if (! imagewebp($target, $stream, $quality)) {
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
