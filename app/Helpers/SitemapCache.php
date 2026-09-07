<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Cache;

/**
 * Laravel-স্তরের সাইটম্যাপ ক্যাশ।
 *
 * Product create/update/delete-এ `bust()` কল করলে generation কাউন্টার বাড়ে।
 * যেহেতু সব ক্যাশ key-তে generation থাকে ("sitemap:gen:{n}:..."), ফলে O(1)-এ
 * পুরোনো সব চাঙ্ক বাতিল হয়ে যায় — এরপরের হিটে তাজা ডেটা আবার ক্যাশ হয়।
 *
 * আণবিক `increment()`-এর উপর নির্ভর করে না — তাই `CACHE_STORE=database`
 * (ডিফল্ট কনফিগ) এবং Redis/Memcached-সবেতেই নির্ভরযোগ্য।
 */
class SitemapCache
{
    /** generation কাউন্টারের TTL — এক দিন; প্রতি bust-এ রিফ্রেশ হয়। */
    protected const TTL_SECONDS = 86_400;

    protected const GENERATION_KEY = 'sitemap:generation';

    /**
     * বর্তমান generation পড়ে; না থাকলে 0 ধরে সেট দিয়ে দেয়।
     */
    public static function generation(): int
    {
        $value = Cache::get(self::GENERATION_KEY);

        if ($value === null) {
            Cache::put(self::GENERATION_KEY, 0, self::TTL_SECONDS);

            return 0;
        }

        return (int) $value;
    }

    /**
     * generation-প্রিফিক্সড ক্যাশ key বানায় — count/page-ভেদে আলাদা slug।
     */
    public static function key(string $key): string
    {
        return 'sitemap:gen:'.self::generation().':'.$key;
    }

    /**
     * Product write-এ কল করবেন — পুরোনো count + সব page ক্যাশ বাতিল হয়।
     */
    public static function bust(): void
    {
        $next = self::generation() + 1;
        Cache::put(self::GENERATION_KEY, $next, self::TTL_SECONDS);
    }
}