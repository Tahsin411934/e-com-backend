# Banner image optimization

Banner uploads now use the shared `App\Services\ImageUploadService` banner
profile. See [shared image uploads](image-uploads.md) for all image types.

New JPEG/PNG banner uploads are resized proportionally to fit within 1920x1080
(no upscaling), encoded as WebP at quality 80, and saved only if smaller than the
original. PNG transparency is retained. Upload paths are unique and public.

Enable PHP GD with WebP support in the production PHP-FPM runtime for upload
compression. Without GD, uploads continue working and retain their original format.
GIF, SVG, existing WebP, JPEGs requiring EXIF rotation, and images over 16 million
pixels are preserved. Upload validation and tenant ownership checks are unchanged.

Existing files are not rewritten. The central Next.js frontend now optimizes them
through next/image, serves responsive variants, preloads the first banner and caches
optimized copies for at least one day. It requires the source URLs to be publicly
accessible; source 403 responses must be fixed on the storage server.

Checks (without Laravel dependencies):

```
php tests/banner-image-optimizer.php
```

Run with GD enabled to also check actual resizing, WebP output, transparency,
no upscaling and unsupported-format fallback.
