<?php
namespace App\Helpers;

class UrlHelper
{
    private const string FALLBACK = 'n-a';

    /**
     * Converts a raw title into a URL-safe slug.
     * Example: "1 & 2 Samuel: King David's Reign!" -> "1-2-samuel-king-davids-reign"
     */
    public static function slugify(string $text): string
    {
        $text = str_replace('&', 'and', $text);

        // Replace non-letter or digits by a single dash (null on invalid UTF-8)
        $slug = preg_replace('~[^\pL\d]+~u', '-', $text);

        if ($slug === null) {
            return self::FALLBACK;
        }

        // Transliterate to ASCII characters if any accents exist (false on failure)
        $ascii = iconv('utf-8', 'us-ascii//TRANSLIT', $slug);
        $slug  = $ascii === false ? $slug : $ascii;

        // Remove unwanted characters, then trim and collapse dashes
        $slug = preg_replace('~[^-\w]+~', '', $slug) ?? '';
        $slug = preg_replace('~-+~', '-', trim($slug, '-')) ?? '';
        $slug = strtolower($slug);

        // === '' rather than empty(): empty() would also reject a title of "0"
        return $slug === '' ? self::FALLBACK : $slug;
    }
}
