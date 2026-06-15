{{-- =========================================================
     VIDEO FACADE (click-to-load)
     Lightweight placeholder that replaces an eager Vimeo/YouTube iframe.
     Shows a local poster image + a play button; the real <iframe> is injected
     by assets/js/video-facade.js only when the user clicks/activates it.

     Props:
       $id       — Vimeo or YouTube video id (string)
       $title    — accessible label / iframe title (already localized by caller)
       $provider — 'vimeo' (default) or 'youtube'
       $params   — optional iframe query string (without leading '?'); a sensible
                   provider default is used when omitted.
       $poster   — optional poster URL; defaults to the locally-fetched Vimeo thumb.

     Visual: relies on the existing .gls-mv-video / .gls-tmv-video 9:16 box the caller
     wraps this in, so the design is unchanged.
========================================================= --}}
@props([
    'id',
    'title' => '',
    'provider' => 'vimeo',
    'params' => null,
    'poster' => null,
])

@php
    $provider = $provider === 'youtube' ? 'youtube' : 'vimeo';

    if ($provider === 'vimeo') {
        $defaultParams = 'title=0&byline=0&portrait=0&badge=0&autopause=0&player_id=0&app_id=58479';
        $localPoster = public_path('assets/images/video-thumbs/' . $id . '.jpg');
        $posterUrl = $poster
            ?: (is_file($localPoster)
                ? asset('assets/images/video-thumbs/' . $id . '.jpg')
                : 'https://vumbnail.com/' . $id . '.jpg');
    } else {
        $defaultParams = 'rel=0&modestbranding=1';
        $posterUrl = $poster ?: ('https://i.ytimg.com/vi/' . $id . '/hqdefault.jpg');
    }

    $iframeParams = $params ?: $defaultParams;
    $label = $title !== '' ? $title : 'Video';
    $playAria = \Illuminate\Support\Facades\Lang::has('home.video_facade.play_aria')
        ? __('home.video_facade.play_aria', ['title' => $label])
        : 'Play video: ' . $label;
@endphp

<button type="button" class="gls-video-facade" data-video-provider="{{ $provider }}"
    data-video-id="{{ $id }}" data-video-params="{{ $iframeParams }}"
    aria-label="{{ $playAria }}">
    <img src="{{ $posterUrl }}" alt="{{ $label }}" class="gls-video-facade__poster" loading="lazy"
        decoding="async" width="720" height="1280">
    <span class="gls-video-facade__play" aria-hidden="true">
        <svg viewBox="0 0 68 48" width="58" height="40" focusable="false">
            <path d="M66.52 7.74c-.78-2.93-2.49-5.41-5.42-6.19C55.79.13 34 0 34 0S12.21.13 6.9 1.55c-2.93.78-4.63 3.26-5.42 6.19C.06 13.05 0 24 0 24s.06 10.95 1.48 16.26c.78 2.93 2.49 5.41 5.42 6.19C12.21 47.87 34 48 34 48s21.79-.13 27.1-1.55c2.93-.78 4.64-3.26 5.42-6.19C67.94 34.95 68 24 68 24s-.06-10.95-1.48-16.26z" fill="#ff2b2b"></path>
            <path d="M45 24 27 14v20" fill="#fff"></path>
        </svg>
    </span>
</button>
