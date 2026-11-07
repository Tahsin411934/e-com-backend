<?php

require dirname(__DIR__).'/app/Services/ImageOptimizer.php';
require dirname(__DIR__).'/app/Services/ImageUploadService.php';

use App\Services\ImageOptimizer;
use App\Services\ImageUploadService;

function verify(bool $condition, string $message): void
{
    if (! $condition) {
        throw new RuntimeException($message);
    }
}

$optimizer = new ImageOptimizer;
if (! function_exists('imagewebp')) {
    verify($optimizer->optimize(__FILE__) === null, 'Missing GD fallback failed.');
    echo "PASS: uploads remain supported without GD.\n";
    exit;
}

$path = tempnam(sys_get_temp_dir(), 'upload-profile-');
try {
    $image = imagecreatetruecolor(2800, 1400);
    imagefill($image, 0, 0, imagecolorallocate($image, 50, 150, 100));
    imagepng($image, $path, 0);
    $original = file_get_contents($path);
    foreach (ImageUploadService::PROFILES as $profile => [$width, $height, $quality]) {
        $output = $optimizer->optimize($path, $width, $height, $quality);
        verify($output !== null && strlen($output) < strlen($original), $profile.' must shrink.');
        $size = getimagesizefromstring($output);
        verify($size[0] <= $width && $size[1] <= $height, $profile.' exceeds its bounds.');
        verify($size[0] === $size[1] * 2, $profile.' lost aspect ratio.');
        verify($size['mime'] === 'image/webp', $profile.' is not WebP.');
        echo 'PASS: '.$profile.' '.$size[0].'x'.$size[1].' '.strlen($output)." bytes.\n";
    }

    imagejpeg($image, $path, 100);
    verify($optimizer->optimize($path) !== null, 'JPEG must convert when smaller.');
    imagedestroy($image);

    // Insert APNG animation control before IDAT: conversion must preserve the upload.
    $payload = pack('NN', 2, 0);
    $chunk = pack('N', strlen($payload)).'acTL'.$payload.pack('N', crc32('acTL'.$payload));
    file_put_contents($path, substr($original, 0, 33).$chunk.substr($original, 33));
    verify($optimizer->optimize($path) === null, 'Animated PNG must not be flattened.');
    echo "PASS: JPEG conversion and APNG preservation.\n";
} finally {
    unlink($path);
}
