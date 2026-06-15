<footer class="site-footer mt-5 {{ app()->getLocale() == 'ar' ? 'rtl' : '' }}">
    <div class="container footer-inner pt-5 pb-0">

        {{-- ===== Footer Intro (text left + logo right; logo first on mobile) ===== --}}
        <div class="footer-intro row align-items-center mb-3 g-3 {{ app()->getLocale() == 'ar' ? 'rtl' : '' }}">
            <div class="col-12 col-md-7 order-2 order-md-1 footer-intro-text {{ app()->getLocale() == 'ar' ? 'text-end' : '' }}">
                <p class="mb-0">
                    {{ __('footer.intro_text') }}
                </p>
            </div>
            <div class="col-12 col-md-5 order-1 order-md-2 footer-intro-logo text-center {{ app()->getLocale() == 'ar' ? 'text-md-start' : 'text-md-end' }}">
                <img src="{{ asset('assets/images/logo/gls-blanc.webp') }}" alt="GLS Sprachenzentrum Logo" loading="lazy" decoding="async" width="220" height="60">
            </div>
        </div>

        {{-- ===== Footer Columns ===== --}}
        <div class="row footer-columns pt-3 border-top border-dark">

            {{-- Column 1 – About --}}
            <div class="col-6 col-md-3 mb-4">
                <p class="footer-title">{{ __('footer.about') }}</p>
                <ul class="list-unstyled footer-links">
                    <li>
                        <a href="{{ LaravelLocalization::localizeUrl(route('front.about')) }}">
                            {{ __('footer.about_us') }}
                        </a>
                    </li>

                    <li>
                        <a href="{{ LaravelLocalization::localizeUrl(route('front.faq')) }}">
                            {{ __('footer.faq') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ LaravelLocalization::localizeUrl(route('front.contact')) }}">
                            {{ __('footer.contact') }}
                        </a>
                    </li>

                    <li>
                        <a href="{{ LaravelLocalization::localizeUrl(route('front.partners.fc_marokko')) }}">
                            {{ __('footer.partner_fc_marokko') }}
                        </a>
                    </li>

                    <li>
                        <a href="{{ LaravelLocalization::localizeUrl(route('front.discover-your-level')) }}">
                            {{ __('footer.discover_your_level') }}
                        </a>
                    </li>
                </ul>
            </div>

            {{-- Column 2 – Our Sites (All cities) --}}
            <div class="col-6 col-md-3 mb-4">
                <p class="footer-title">{{ __('footer.our_sites') }}</p>
                <ul class="list-unstyled footer-links">
                    {{-- IMPORTANT: /sites/{slug} --}}
                    <li>
                        <a href="{{ LaravelLocalization::localizeUrl(route('front.sites.show', 'casablanca')) }}">
                            {{ __('footer.sites.casablanca') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ LaravelLocalization::localizeUrl(route('front.sites.show', 'marrakech')) }}">
                            {{ __('footer.sites.marrakech') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ LaravelLocalization::localizeUrl(route('front.sites.show', 'rabat')) }}">
                            {{ __('footer.sites.rabat') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ LaravelLocalization::localizeUrl(route('front.sites.show', 'kenitra')) }}">
                            {{ __('footer.sites.kenitra') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ LaravelLocalization::localizeUrl(route('front.sites.show', 'sale')) }}">
                            {{ __('footer.sites.sale') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ LaravelLocalization::localizeUrl(route('front.sites.show', 'agadir')) }}">
                            {{ __('footer.sites.agadir') }}
                        </a>
                    </li>

                    {{-- Online (redirects already exist) --}}
                    <li>
                        <a href="{{ LaravelLocalization::localizeUrl(route('front.online-courses')) }}">
                            {{ __('footer.sites.online') }}
                        </a>
                    </li>
                </ul>
            </div>

            {{-- Column 3 – Courses + Levels A1-B2 --}}
            <div class="col-6 col-md-3 mb-4">
                <p class="footer-title">{{ __('footer.german_courses') }}</p>
                <ul class="list-unstyled footer-links">
                    <li>
                        <a href="{{ LaravelLocalization::localizeUrl(route('front.intensive-courses')) }}">
                            {{ __('footer.intensive_courses') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ LaravelLocalization::localizeUrl(route('front.online-courses')) }}">
                            {{ __('footer.online_courses') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ LaravelLocalization::localizeUrl(route('front.pricing')) }}">
                            {{ __('footer.pricing') }}
                        </a>
                    </li>

                    <li class="mt-2">
                        <span class="footer-title d-block mb-1" style="font-size: 0.95rem;">
                            {{ __('footer.levels') }}
                        </span>
                    </li>

                    <li>
                        <a href="{{ LaravelLocalization::localizeUrl(route('front.niveaux.a1')) }}">{{ __('footer.level_links.a1') }}</a>
                    </li>
                    <li>
                        <a href="{{ LaravelLocalization::localizeUrl(route('front.niveaux.a2')) }}">{{ __('footer.level_links.a2') }}</a>
                    </li>
                    <li>
                        <a href="{{ LaravelLocalization::localizeUrl(route('front.niveaux.b1')) }}">{{ __('footer.level_links.b1') }}</a>
                    </li>
                    <li>
                        <a href="{{ LaravelLocalization::localizeUrl(route('front.niveaux.b2')) }}">{{ __('footer.level_links.b2') }}</a>
                    </li>
                </ul>
            </div>

            {{-- Column 4 – Exams + Resources --}}
            <div class="col-6 col-md-3 mb-4">
                <p class="footer-title">{{ __('footer.resources') }}</p>
                <ul class="list-unstyled footer-links">
                    <li>
                        <a href="{{ LaravelLocalization::localizeUrl(route('front.exams.gls')) }}">
                            {{ __('footer.exams_gls') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ LaravelLocalization::localizeUrl(route('front.exams.osd')) }}">
                            {{ __('footer.exams_osd') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ LaravelLocalization::localizeUrl(route('front.exams.goethe')) }}">
                            {{ __('footer.exams_goethe') }}
                        </a>
                    </li>

                    <li class="mt-2">
                        <a href="{{ LaravelLocalization::localizeUrl(route('front.studienkollegs')) }}">
                            {{ __('footer.studienkollegs') }}
                        </a>
                    </li>

                    <li>
                        <a href="{{ LaravelLocalization::localizeUrl(route('blog.index')) }}">
                            {{ __('footer.blog') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ LaravelLocalization::localizeUrl(route('front.student-stories')) }}">
                            {{ __('footer.student_stories') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ LaravelLocalization::localizeUrl(route('front.certificate.check')) }}">
                            {{ __('footer.certificate_check') }}
                        </a>
                    </li>
                </ul>
            </div>


        </div>

        {{-- ===== Newsletter (compact, full width above bottom bar) ===== --}}
        <div class="footer-newsletter mt-3 pt-3 border-top border-dark {{ app()->getLocale() == 'ar' ? 'rtl' : '' }}">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2">
                <div class="footer-newsletter-copy">
                    <p class="footer-title mb-1">{{ __('footer.newsletter.title') }}</p>
                    <p class="mb-0 small text-muted-light">{{ __('footer.newsletter.text') }}</p>
                </div>
                <form id="newsletterForm" class="footer-newsletter-form d-flex gap-2"
                    action="{{ route('newsletter.subscribe') }}" method="POST">
                    @csrf
                    <input type="hidden" name="source" value="footer">
                    <input id="newsletterEmail" type="email" name="email" class="form-control form-control-sm"
                        placeholder="{{ __('footer.newsletter.placeholder') }}" required autocomplete="email">
                    <button id="newsletterBtn" type="submit" class="btn btn-light btn-sm">
                        {{ __('footer.newsletter.button') }}
                    </button>
                </form>
            </div>
            <div id="newsletterMsg" class="small mt-1"></div>
        </div>
    </div>

    {{-- ===== Footer Bottom ===== --}}
    <div class="footer-bottom">
        <div
            class="container d-flex flex-column flex-md-row justify-content-between align-items-center py-3 small text-center text-md-start">
            <div class="footer-legal {{ app()->getLocale() == 'ar' ? 'text-end' : '' }}">
                <a href="{{ LaravelLocalization::localizeUrl(route('front.terms')) }}">
                    {{ __('footer.terms') }}
                </a>

                <a href="{{ LaravelLocalization::localizeUrl(route('front.privacy')) }}">
                    {{ __('footer.privacy') }}
                </a>

                <a href="#" data-open-cookies>{{ __('footer.cookies') }}</a>

            </div>

            <div class="footer-brand mt-2 mt-md-0">
                {{ __('footer.copyright') }}
            </div>

            @include('frontoffice.partials.svg-sstars')

        </div>
    </div>

    <script src="{{ asset('assets/js/newsletter.js') }}" defer></script>
    {{-- Tawk.to live chat is loaded by assets/js/consent-loader.js ONLY after the visitor
         accepts cookies (then after a short idle delay). The previous inline embed loaded
         on every page view and used crossorigin='*', which threw a CORS console error. --}}

</footer>
