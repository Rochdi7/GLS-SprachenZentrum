@php
    $locale = app()->getLocale();
    $defaults = config("seo.defaults.{$locale}", config('seo.defaults.fr'));
    // yieldContent() returns HTML-escaped output (Blade escapes the value form of @section),
    // so decode once here — the {{ }} below re-escapes it. Without this, apostrophes and
    // accents render as raw entities (l&#039;allemand) in the tab title and in SERPs.
    $decodeMeta = fn ($value) => trim(html_entity_decode((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8'));

    $seoTitle = $decodeMeta($__env->yieldContent('title')) ?: ($defaults['title'] ?? 'GLS Sprachenzentrum');
    $seoDescription = $decodeMeta($__env->yieldContent('meta_description')) ?: ($defaults['description'] ?? '');
    $canonicalUrl = LaravelLocalization::getLocalizedURL($locale, null, [], true);
    $ogImage = asset(ltrim(config('seo.og_image', '/assets/images/IMG_4399.avif'), '/'));
    $ogLocaleMap = ['fr' => 'fr_FR', 'en' => 'en_US', 'de' => 'de_DE', 'ar' => 'ar_MA'];
    $ogLocale = $ogLocaleMap[$locale] ?? 'fr_FR';
@endphp

<title>{{ $seoTitle }}</title>
@if ($seoDescription !== '')
    <meta name="description" content="{{ $seoDescription }}">
@endif

<link rel="canonical" href="{{ $canonicalUrl }}">
@foreach (LaravelLocalization::getSupportedLocales() as $localeCode => $properties)
    <link rel="alternate" hreflang="{{ $localeCode }}"
        href="{{ LaravelLocalization::getLocalizedURL($localeCode, null, [], true) }}">
@endforeach
<link rel="alternate" hreflang="x-default" href="{{ LaravelLocalization::getLocalizedURL('fr', null, [], true) }}">

<meta property="og:type" content="website">
<meta property="og:site_name" content="GLS Sprachenzentrum">
<meta property="og:title" content="{{ $seoTitle }}">
@if ($seoDescription !== '')
    <meta property="og:description" content="{{ $seoDescription }}">
@endif
<meta property="og:url" content="{{ $canonicalUrl }}">
<meta property="og:locale" content="{{ $ogLocale }}">
@foreach (LaravelLocalization::getSupportedLocales() as $localeCode => $properties)
    @if ($localeCode !== $locale)
        <meta property="og:locale:alternate" content="{{ $ogLocaleMap[$localeCode] ?? $localeCode }}">
    @endif
@endforeach
<meta property="og:image" content="{{ $ogImage }}">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $seoTitle }}">
@if ($seoDescription !== '')
    <meta name="twitter:description" content="{{ $seoDescription }}">
@endif
<meta name="twitter:image" content="{{ $ogImage }}">
@php $twitterSite = config('seo.twitter.site'); @endphp
@if ($twitterSite)
    <meta name="twitter:site" content="{{ $twitterSite }}">
@endif

@stack('seo-schema')
