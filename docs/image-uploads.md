# Shared image uploads

`App\Services\ImageUploadService` is used for product/gallery, product requests,
category images, brand logos, site settings, homepage CTA images/banners,
campaign banners and hero banners. Existing URL prefixes and tenant ownership
logic remain in their original services; the uploader returns a public-disk path.

| Profile | Maximum bounds | WebP quality |
| --- | --- | --- |
| Product/gallery/product request | 2400x2400 | 85 |
| Banner/campaign | 1920x1080 | 80 |
| Category | 800x800 | 82 |
| Brand logo | 800x800 | 85 |
| Site settings | 1600x1600 | 85 |
| CTA image | 1600x1600 | 82 |

Bounds preserve aspect ratio and never enlarge small images. JPEG/PNG uploads
become WebP only when the result is smaller. PNG transparency is preserved.
SVG, GIF, WebP, APNG, JPEGs needing EXIF orientation changes, and images above
16 million pixels retain their original content. New filenames are unique.
New setting keys/files still use existing validation and store scoping.

Enable GD with WebP in the production PHP-FPM runtime. If GD is absent, original
uploads continue to work. No new Composer package or database migration is required.
Existing files are not rewritten or removed by this change; reupload to optimize
their stored source, or continue using the frontend's responsive optimization.

Run standalone checks with GD enabled:

```
php tests/image-upload-profiles.php
php tests/banner-image-optimizer.php
```

Laravel storage checks: `php artisan test --filter=ImageUploadServiceTest`.
