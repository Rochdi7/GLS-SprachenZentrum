{{-- Real Google reviews of one centre (config/google_reviews.php). Usage: @include('frontoffice.partials.google-reviews-centre', ['slug' => 'rabat']) --}}
@php
    $gr = \App\Support\GoogleReviews::centre($slug);
    $grCity = \Illuminate\Support\Facades\Lang::has('gls.sites.'.$slug) ? __('gls.sites.'.$slug) : ($gr['city'] ?? '');
@endphp
@if ($gr)
    <section class="reviews-carousel-section section {{ app()->getLocale() == 'ar' ? 'rtl' : '' }} reveal delay-1" aria-labelledby="google-reviews-{{ $slug }}">
        <div class="container is-reviews-title-block reveal delay-2">
            <h2 id="google-reviews-{{ $slug }}" class="h-section-subtitle is-reviews reveal fade-blur-title delay-1">
                {{ __('google-reviews.centre_title', ['city' => $grCity]) }}
            </h2>
            <div class="div-block-29 w-inline-block reveal delay-3">
                <div class="reveal delay-1">@include('frontoffice.partials.svg-stars-big')</div>
                <div class="reveal delay-2"><strong>{{ __('google-reviews.centre_summary', [
                    'rating' => \App\Support\GoogleReviews::number($gr['rating'], 1),
                    'count' => \App\Support\GoogleReviews::number($gr['count']),
                ]) }}</strong></div>
            </div>
        </div>

        <div class="container">
            <div class="row g-4">
                @foreach ($gr['reviews'] as $review)
                    <div class="col-md-6 col-lg-4 d-flex">
                        <div class="review-block review-card-inspired reveal delay-2" style="width:100%;margin-right:0;">
                            <div class="review-stars small fw-bold reveal delay-3">{{ __('google-reviews.source') }} · GLS {{ $grCity }}</div>
                            <div class="text-block-9 reveal delay-1" lang="{{ $review['lang'] }}" dir="auto">"{{ $review['text'] }}"</div>
                            <div class="text-block-10 reveal delay-2" dir="auto">– {{ $review['name'] }} ({{ $review['year'] }})</div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="text-center mt-4">
                <a href="{{ $gr['profile'] }}" target="_blank" rel="noopener" class="button is-white reveal delay-1">
                    {{ __('google-reviews.see_all') }}
                </a>
            </div>
        </div>
    </section>
@endif
