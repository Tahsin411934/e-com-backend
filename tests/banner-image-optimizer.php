<?php

// Standalone image regression checks: no Laravel/vendor bootstrap required.
require dirname(__DIR__).'/Modules/Frontend/app/Services/BannerImageOptimizer.php';

use Modules\Frontend\Services\BannerImageOptimizer;

function check(bool $condition, string $message): void
{
    if (! $condition) {
        throw new RuntimeException($message);
    }
}

$optimizer = new BannerImageOptimizer;
if (! function_exists('imagewebp')) {
    check($optimizer->optimize(__FILE__) === null, 'Missing GD must preserve uploads.');
    echo "PASS: missing GD safely preserves original uploads.\n";
    exit;
}

$path = tempnam(sys_get_temp_dir(), 'banner-test-');
try {
    $image = imagecreatetruecolor(2400, 800);
    imagefill($image, 0, 0, imagecolorallocate($image, 40, 150, 100));
    imagepng($image, $path, 0);
    imagedestroy($image);
    $originalBytes = filesize($path);
    $encoded = $optimizer->optimize($path);
    check($encoded !== null && strlen($encoded) < $originalBytes, 'Large banner must shrink.');
    $size = getimagesizefromstring($encoded);
    check($size[0] === 1920 && $size[1] === 640, 'Aspect ratio must be retained.');
    check($size['mime'] === 'image/webp', 'Output must be WebP.');
    echo 'PASS: large PNG '.$originalBytes.' bytes -> '.strlen($encoded)." bytes, 1920x640 WebP.\n";

    $image = imagecreatetruecolor(400, 200);
    imagealphablending($image, false);
    imagesavealpha($image, true);
    imagefill($image, 0, 0, imagecolorallocatealpha($image, 0, 0, 0, 127));
    imagepng($image, $path, 0);
    imagedestroy($image);
    $encoded = $optimizer->optimize($path);
    check($encoded !== null, 'Transparent PNG should be optimized.');
    $decoded = imagecreatefromstring($encoded);
    check(imagesx($decoded) === 400 && imagesy($decoded) === 200, 'Small image must not upscale.');
    check((imagecolorat($decoded, 0, 0) >> 24) === 127, 'Transparency must be retained.');
    imagedestroy($decoded);
    echo "PASS: transparency retained, no upscaling.\n";

    file_put_contents($path, $encoded);
    check($optimizer->optimize($path) === null, 'Existing WebP must remain unchanged.');
    file_put_contents($path, '<svg xmlns="http://www.w3.org/2000/svg"/>');
    check($optimizer->optimize($path) === null, 'SVG must remain unchanged.');
    file_put_contents($path, 'invalid image');
    check($optimizer->optimize($path) === null, 'Invalid image must not crash the optimizer.');
    echo "PASS: WebP/SVG/invalid input fallback.\n";
} finally {
    unlink($path);
}
