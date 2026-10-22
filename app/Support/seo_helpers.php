<?php

use Illuminate\Support\Facades\Lang;

if (! function_exists('seo_trans')) {
    /**
     * Translate an SEO key only if it exists in the CURRENT locale.
     *
     * Laravel's __() falls back to the fallback locale (fr) when a key is missing,
     * which would put a French title or meta description on an English, German or
     * Arabic page. For SEO metadata that is worse than no value: an empty value lets
     * seo-head.blade.php fall back to that locale's own default from config/seo.php.
     *
     * @param  array<string,string>  $replace
     */
    function seo_trans(string $key, string $default = '', array $replace = []): string
    {
        if (! Lang::has($key, app()->getLocale(), false)) {
            return $default;
        }

        $value = __($key, $replace);

        return is_string($value) ? $value : $default;
    }
}
