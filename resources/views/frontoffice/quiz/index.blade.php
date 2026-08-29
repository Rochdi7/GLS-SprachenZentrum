@extends('frontoffice.layouts.app')

@section('title', $quiz['title'] ?? 'Quiz')

<link rel="stylesheet" href="{{ asset('assets/css/frontoffice/quiz/quiz.css') }}">

@section('content')

    @php
        $quiz = $quiz ?? ['title' => 'Quiz', 'subtitle' => null, 'questions' => []];
        $timeLimitSeconds = (int) ($quiz['time_limit_seconds'] ?? 0);
        $remainingSeconds = (int) ($quiz['remaining_seconds'] ?? $timeLimitSeconds);
        $questionCount = count($quiz['questions'] ?? []);
        $perQuestion = ($timeLimitSeconds > 0 && $questionCount > 0)
            ? (int) ceil($timeLimitSeconds / $questionCount)
            : 25;
    @endphp

    <section class="quiz-page">
        <div class="quiz-overlay" aria-hidden="true"></div>

        <div class="quiz-shell" data-quiz>
            <div class="quiz-topbar">
                <div class="quiz-brand">
                    <span class="quiz-brand_dot"></span>
                    <span class="quiz-brand_text">{{ $quiz['title'] ?? 'Quiz' }}</span>
                </div>

                {{-- TOOLS BAR --}}
                <div class="quiz-meta">
                    {{-- Timer (formatted as MM:SS) --}}
                    @php
                        $timerMin = str_pad(intdiv($remainingSeconds, 60), 2, '0', STR_PAD_LEFT);
                        $timerSec = str_pad($remainingSeconds % 60, 2, '0', STR_PAD_LEFT);
                    @endphp
                    <span class="quiz-meta_label" data-quiz-timer hidden aria-live="polite">
                        {{ $timerMin }}:{{ $timerSec }}
                    </span>

                    {{-- Font size controls --}}
                    <div class="quiz-font-controls" data-quiz-font-controls>
                        <button type="button" class="quiz-tool-btn" data-quiz-font-down title="{{ __('quiz/level_test.interface.font_down') }}" aria-label="{{ __('quiz/level_test.interface.font_down') }}">A-</button>
                        <span class="quiz-font-label" data-quiz-font-label>100%</span>
                        <button type="button" class="quiz-tool-btn" data-quiz-font-up title="{{ __('quiz/level_test.interface.font_up') }}" aria-label="{{ __('quiz/level_test.interface.font_up') }}">A+</button>
                    </div>

                    {{-- Fullscreen toggle --}}
                    <button type="button" class="quiz-tool-btn" data-quiz-fullscreen aria-pressed="false" title="{{ __('quiz/level_test.interface.fullscreen') }}">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M2 6V2h4M10 2h4v4M14 10v4h-4M6 14H2v-4"/></svg>
                    </button>

                    {{-- Navigator toggle --}}
                    <button type="button" class="quiz-tool-btn" data-quiz-navigator-toggle aria-expanded="false" title="{{ __('quiz/level_test.interface.navigator') }}">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><rect x="1" y="1" width="4" height="4" rx="1"/><rect x="6" y="1" width="4" height="4" rx="1"/><rect x="11" y="1" width="4" height="4" rx="1"/><rect x="1" y="6" width="4" height="4" rx="1"/><rect x="6" y="6" width="4" height="4" rx="1"/><rect x="11" y="6" width="4" height="4" rx="1"/><rect x="1" y="11" width="4" height="4" rx="1"/><rect x="6" y="11" width="4" height="4" rx="1"/><rect x="11" y="11" width="4" height="4" rx="1"/></svg>
                    </button>

                    {{-- Counter --}}
                    <span class="quiz-meta_label" data-quiz-counter></span>
                </div>
            </div>

            {{-- QUESTION NAVIGATOR PANEL (dropdown) --}}
            <div class="quiz-navigator-panel" data-quiz-navigator-panel hidden>
                <div class="quiz-navigator-header">
                    <span class="quiz-navigator-title">{{ __('quiz/level_test.interface.navigator_title') }}</span>
                    <div class="quiz-navigator-legend">
                        <span class="quiz-legend-item"><span class="quiz-legend-dot is-answered"></span> {{ __('quiz/level_test.interface.legend_answered') }}</span>
                        <span class="quiz-legend-item"><span class="quiz-legend-dot is-flagged"></span> {{ __('quiz/level_test.interface.legend_flagged') }}</span>
                        <span class="quiz-legend-item"><span class="quiz-legend-dot is-unanswered"></span> {{ __('quiz/level_test.interface.legend_unanswered') }}</span>
                    </div>
                </div>
                <div class="quiz-navigator-grid" data-quiz-navigator></div>
            </div>

            <div class="quiz-card" role="region" aria-label="Quiz">
                <div class="quiz-card_inner">

                    {{-- EXAM TOOLBAR (visible during questions) --}}
                    <div class="quiz-toolbar" data-quiz-toolbar hidden>
                        <div class="quiz-toolbar_left">
                            <button type="button" class="quiz-flag-btn" data-quiz-flag aria-pressed="false" title="{{ __('quiz/level_test.interface.flag_btn') }}">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 1v14M3 1h9l-2.5 4L12 9H3"/></svg>
                                <span>{{ __('quiz/level_test.interface.flag_btn') }}</span>
                            </button>
                        </div>
                        <div class="quiz-toolbar_right">
                            {{-- Progress bar --}}
                            <div class="quiz-progressbar" data-quiz-progressbar>
                                <div class="quiz-progressbar_track">
                                    <div class="quiz-progressbar_fill" data-quiz-progressbar-fill></div>
                                </div>
                                <span class="quiz-progressbar_text" data-quiz-progressbar-text>0 / 0 (0%)</span>
                            </div>
                        </div>
                    </div>

                    {{-- Start screen --}}
                    <div class="quiz-screen {{ empty($quizResult) ? 'is-active' : '' }}" data-screen="start">

                        {{-- GLS HEADER (à cacher après start) --}}
                        <div class="quiz-hero" data-quiz-hero>
                            <img src="{{ asset('build/images/logo/gls-noir.png') }}" alt="GLS Sprachenzentrum"
                                class="quiz-hero_logo" loading="eager" />

                            <h1 class="quiz-hero_title">
                                {{ __('quiz/level_test.interface.quiz_title', ['level' => $quizLevel ?? 'A1']) }}
                            </h1>

                            <p class="quiz-hero_subtitle">
                                {{ __('quiz/level_test.interface.hero_subtitle') }}<br>
                                <span>{{ __('quiz/level_test.interface.hero_subtitle_en') }}</span>
                            </p>
                        </div>

                        @if (!empty($quiz['subtitle']))
                            <p class="quiz-subtitle" data-quiz-subtitle>
                                {{ $quiz['subtitle'] }}
                            </p>
                        @endif

                        @if($timeLimitSeconds > 0)
                            <div class="quiz-exam-info">
                                <span class="quiz-exam-info_item">
                                    <strong>{{ $questionCount }}</strong> {{ __('quiz/level_test.interface.questions') }}
                                </span>
                                <span class="quiz-exam-info_sep">&middot;</span>
                                <span class="quiz-exam-info_item">
                                    <strong>{{ intdiv($timeLimitSeconds, 60) }}</strong> {{ __('quiz/level_test.interface.minutes') }}
                                </span>
                                <span class="quiz-exam-info_sep">&middot;</span>
                                <span class="quiz-exam-info_item">
                                    <strong>{{ $perQuestion }}</strong> {{ __('quiz/level_test.interface.sec_per_question') }}
                                </span>
                            </div>
                        @endif

                        <div class="quiz-start_actions">
                            <button type="button" class="quiz-btn quiz-btn_primary" data-quiz-start>
                                {{ __('quiz/level_test.interface.start_btn') }}
                            </button>
                        </div>

                        <div class="quiz-shortcuts-hint">
                            <p class="quiz-shortcuts-title">{{ __('quiz/level_test.interface.shortcuts_title') }}</p>
                            <div class="quiz-shortcuts-grid">
                                <span><kbd>1</kbd>-<kbd>4</kbd> {{ __('quiz/level_test.interface.shortcut_select') }}</span>
                                <span><kbd>Enter</kbd> {{ __('quiz/level_test.interface.shortcut_next') }}</span>
                                <span><kbd>R</kbd> {{ __('quiz/level_test.interface.shortcut_prev') }}</span>
                                <span><kbd>F</kbd> {{ __('quiz/level_test.interface.shortcut_flag') }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Question screen --}}
                    <div class="quiz-screen" data-screen="question">
                        <div class="quiz-q_head">
                            <div class="quiz-q_kicker" data-q-title></div>
                            <div class="quiz-q_prompt" data-q-prompt></div>
                        </div>

                        <div class="quiz-q_body">
                            <div class="quiz-q_text" data-q-question></div>

                            <div class="quiz-q_media" data-q-media hidden>
                                <img data-q-image alt="" loading="lazy" hidden>
                                <audio data-q-audio controls preload="none" hidden></audio>
                            </div>

                            <div class="quiz-answers" data-q-answers></div>
                        </div>
                    </div>

                    {{-- Result screen --}}
                    @php
                        $isGood = !empty($quizResult) && ($quizResult['percent'] ?? 0) >= 50;
                    @endphp
                    <div class="quiz-screen {{ !empty($quizResult) ? 'is-active' : '' }}" data-screen="result">
                        <div class="quiz-result">
                            <img src="{{ asset('build/images/logo/gls-noir.png') }}" alt="GLS Sprachenzentrum"
                                class="quiz-result_logo" loading="lazy" />

                            @if(!empty($quizResult))
                                {{-- Server-side result --}}
                                <h2 class="quiz-result_title" style="{{ $isGood ? '' : 'color: var(--quiz-danger, #dc3545);' }}">
                                    {{ $isGood ? __('quiz/level_test.interface.result_title_good') : __('quiz/level_test.interface.result_title_bad') }}
                                </h2>

                                <p class="quiz-result_points">
                                    {!! __('quiz/level_test.interface.result_text', [
                                        'correct' => '<strong>' . ($quizResult['correct'] ?? 0) . '</strong>',
                                        'total' => '<strong>' . ($quizResult['total'] ?? 0) . '</strong>',
                                        'percent' => '<strong>' . ($quizResult['percent'] ?? 0) . '</strong>',
                                    ]) !!}
                                </p>

                                {{-- Level detection removed --}}
                            @else
                                {{-- JS placeholder (briefly visible before form submit) --}}
                                <h2 class="quiz-result_title" data-result-title>
                                    ...
                                </h2>
                                <p class="quiz-result_points" data-result-text>
                                    {!! __('quiz/level_test.interface.result_text', ['correct' => '<span data-result-correct>...</span>', 'total' => '<span data-result-total>0</span>', 'percent' => '<span data-result-percent>...</span>']) !!}
                                </p>
                                {{-- Level detection removed --}}
                            @endif

                            <div class="quiz-result_actions">
                                <button type="button" class="quiz-btn" data-quiz-restart>
                                    {{ __('quiz/level_test.interface.result_restart') }}
                                </button>

                                <a href="{{ route('front.pricing') }}" class="quiz-btn quiz-btn_primary">
                                    {{ __('quiz/level_test.interface.result_courses') }}
                                </a>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="quiz-nav" {!! !empty($quizResult) ? 'style="display:none"' : '' !!}>
                    <button type="button" class="quiz-nav_btn" data-quiz-prev disabled>
                        <span class="quiz-nav_arrow">←</span>
                        <span class="quiz-nav_text">{{ __('quiz/level_test.interface.nav_prev') }}</span>
                    </button>

                    <button type="button" class="quiz-nav_btn is-right" data-quiz-next disabled>
                        <span class="quiz-nav_text">{{ __('quiz/level_test.interface.nav_next') }}</span>
                        <span class="quiz-nav_arrow">→</span>
                    </button>
                </div>
            </div>

            <div class="quiz-progress" aria-label="Progression" data-quiz-progress></div>

            <form class="quiz-form" data-quiz-form data-no-recaptcha hidden method="POST"
                action="{{ route('front.discover-your-level.answer') }}">
                @csrf
                <input type="hidden" name="quiz" value="{{ $quizLevel ?? 'A1' }}">
                <input type="hidden" name="answers_json" value="" data-answers-json>
            </form>

        </div>
    </section>

    <script>
        window.__QUIZ__ = @json($quiz);
        window.__QUIZ_TIMER__ = {
            enabled: true,
            secondsPerQuestion: {{ $perQuestion }},
            autoNextOnTimeout: true
        };
        window.__QUIZ_LEVEL__ = @json($quizLevel ?? 'A1');
        window.__QUIZ_I18N__ = {
            placeholder: @json(__('quiz/level_test.interface.shortcut_select')),
            flagAdd: @json(__('quiz/level_test.interface.flag_btn')),
        };
        @if(!empty($quizResult))
            window.__QUIZ_HAS_RESULT__ = true;
            {{-- Track quiz completion via Google Ads gtag --}}
            document.addEventListener('DOMContentLoaded', function () {
                if (window.gtag) {
                    gtag('event', 'niveau_test_completed', {
                        quiz_level: @json($quizResult['quiz_level'] ?? $quizLevel),
                        score_correct: {{ $quizResult['correct'] ?? 0 }},
                        score_total: {{ $quizResult['total'] ?? 0 }},
                        score_percent: {{ $quizResult['percent'] ?? 0 }},
                    });
                }
            });
        @endif
    </script>

    <script defer src="{{ asset('assets/js/quiz.js') }}"></script>
    <script defer src="{{ asset('assets/js/quiz-tools.js') }}"></script>
    <script defer src="{{ asset('assets/js/quiz-keyboard.js') }}"></script>
    <script defer src="{{ asset('assets/js/quiz-timer.js') }}"></script>
    <script defer src="{{ asset('assets/js/quiz-fullscreen.js') }}"></script>
    <script defer src="{{ asset('assets/js/quiz-anticheat.js') }}"></script>
@endsection
