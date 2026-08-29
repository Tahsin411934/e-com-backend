<?php

namespace App\Helpers;

class Sanitizer
{
    /**
     * Strip all HTML tags from a string
     */
    public static function stripHtml(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return strip_tags($value);
    }

    /**
     * Strip HTML from all string fields in an array
     */
    public static function sanitizeArray(array $data, array $fields = []): array
    {
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                if (empty($fields) || in_array($key, $fields)) {
                    $data[$key] = self::stripHtml($value);
                }
            }
        }

        return $data;
    }
}
