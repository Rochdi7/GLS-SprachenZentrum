<footer class="site-footer mt-5 {{ app()->getLocale() == 'ar' ? 'rtl' : '' }}">
    <div class="container footer-inner py-5">

        {{-- ===== Footer Intro (with GLS Logo) ===== --}}
        <div class="footer-intro mb-4 {{ app()->getLocale() == 'ar' ? 'text-end' : '' }}">
            <p>
                {{ __('footer.intro_text') }}
            </p>
            <img src="{{ asset('assets/images/logo/gls-blanc.webp') }}" alt="GLS Sprachenzentrum Logo">
        </div>

        {{-- ===== Footer Columns ===== --}}
        <div class="row footer-columns pt-4 border-top border-dark">

            {{-- Column 1 – About --}}
            <div class="col-6 col-md-3 mb-4">
                <h6 class="footer-title">{{ __('footer.about') }}</h6>
                <ul class="list-unstyled footer-links">
                    <li><a href="{{ LaravelLocalization::localizeUrl(route('front.about')) }}">{{ __('footer.about_us') }}</a></li>

                    {{-- OUR SITES submenu --}}
                    <li><a href="#">{{ __('footer.our_sites') }}</a></li>

                    <li><a href="{{ LaravelLocalization::localizeUrl(route('front.faq')) }}">{{ __('footer.faq') }}</a></li>
                    <li><a href="{{ LaravelLocalization::localizeUrl(route('front.contact')) }}">{{ __('footer.contact') }}</a></li>
                </ul>
            </div>

            {{-- Column 2 – German Courses --}}
            <div class="col-6 col-md-3 mb-4">
                <h6 class="footer-title">{{ __('footer.german_courses') }}</h6>
                <ul class="list-unstyled footer-links">
                    <li><a href="{{ LaravelLocalization::localizeUrl(route('front.intensive-courses')) }}">{{ __('footer.intensive_courses') }}</a></li>
                    <li><a href="{{ LaravelLocalization::localizeUrl(route('front.online-courses')) }}">{{ __('footer.online_courses') }}</a></li>
                    <li><a href="{{ LaravelLocalization::localizeUrl(route('front.pricing')) }}">{{ __('footer.pricing') }}</a></li>
                </ul>
            </div>

            {{-- Column 3 – Exams --}}
            <div class="col-6 col-md-3 mb-4">
                <h6 class="footer-title">{{ __('footer.exams') }}</h6>
                <ul class="list-unstyled footer-links">
                    <li><a href="{{ LaravelLocalization::localizeUrl(route('front.exams.gls')) }}">{{ __('footer.goethe_exams') }}</a></li>
                    <li><a href="{{ LaravelLocalization::localizeUrl(route('front.exams.osd')) }}">{{ __('footer.osd_exams') }}</a></li>
                </ul>
            </div>

            {{-- Column 4 – Resources --}}
            <div class="col-6 col-md-3 mb-4">
                <h6 class="footer-title">{{ __('footer.resources') }}</h6>
                <ul class="list-unstyled footer-links">
                    <li>
        <a href="{{ LaravelLocalization::localizeUrl(route('blog.index')) }}">
            {{ __('footer.blog') }}
        </a>
    </li>
                    <li><a href="{{ LaravelLocalization::localizeUrl(route('front.student-stories')) }}">{{ __('footer.student_stories') }}</a></li>
                </ul>
            </div>

        </div>
    </div>

    {{-- ===== Footer Bottom ===== --}}
    <div class="footer-bottom">
        <div class="container d-flex flex-column flex-md-row justify-content-between align-items-center py-3 small text-center text-md-start">

            <div class="footer-legal {{ app()->getLocale() == 'ar' ? 'text-end' : '' }}">
                <a href="#">{{ __('footer.terms') }}</a>
                <a href="#">{{ __('footer.privacy') }}</a>
                <a href="#">{{ __('footer.imprint') }}</a>
            </div>

            <div class="footer-brand mt-2 mt-md-0">
                {{ __('footer.copyright') }}
            </div>

        </div>
    </div>
</footer>
