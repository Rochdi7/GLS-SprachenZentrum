{{-- Reusable list of GLS centres — click to expand → address is a Google Maps link.
     Pass an optional $linkClass to scope the address-link style (default for home/contact;
     LP pages pass 'lp-address-link').
--}}
@php
    $linkClass = $linkClass ?? 'gls-address-link';
    $groupName = 'gls-centers-' . uniqid();
@endphp
<ul class="gls-address-list">
    @foreach(__('home.contact.centers_list') as $slug => $branches)
        <li class="gls-address-row">
            <details class="gls-address-details" name="{{ $groupName }}">
                <summary class="gls-address-summary">
                    <span class="gls-address-city">GLS {{ __('home.contact.centers.' . $slug) }}@if(count($branches) > 1) <span class="gls-address-branch">({{ count($branches) }})</span>@endif</span>
                    <svg class="gls-address-chevron" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <polyline points="6 9 12 15 18 9"/>
                    </svg>
                </summary>
                <div class="gls-address-body">
                    @foreach($branches as $index => $branch)
                        @php
                            $branchName = 'GLS ' . __('home.contact.centers.' . $slug)
                                . ($index === 0 ? '' : ' ' . __('home.contact.branch_label') . ' ' . ($index + 1));
                        @endphp
                        <a class="{{ $linkClass }}"
                            href="{{ $branch['maps_url'] }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            aria-label="{{ $branchName }} — Google Maps">
                            @if(count($branches) > 1)
                                <span class="gls-address-branch-label">{{ $branchName }}</span>
                            @endif
                            <span class="gls-address-text">{{ $branch['address'] }}</span>
                            <svg class="gls-address-arrow" viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <line x1="7" y1="17" x2="17" y2="7"/>
                                <polyline points="7 7 17 7 17 17"/>
                            </svg>
                        </a>
                        @if(!empty($branch['phones']))
                            <div class="gls-address-phones">
                                @foreach($branch['phones'] as $phone)
                                    <a class="gls-address-phone" href="tel:{{ str_replace(' ', '', $phone) }}">
                                        <svg class="gls-address-phone-icon" viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.94.36 1.86.68 2.75a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.33-1.33a2 2 0 0 1 2.11-.45c.89.32 1.81.55 2.75.68A2 2 0 0 1 22 16.92z"/>
                                        </svg>
                                        <span>{{ $phone }}</span>
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    @endforeach
                </div>
            </details>
        </li>
    @endforeach
</ul>

@once
    <script>
        // Single-open accordion fallback for browsers that don't support <details name="...">
        (function () {
            if ('name' in document.createElement('details') === false || (function () {
                // Feature-detect by setting `name` and seeing if it's preserved as an attribute attribute
                const d = document.createElement('details');
                d.setAttribute('name', 'x');
                return d.name === 'x';
            })()) {
                // Native support — nothing to do
                return;
            }
            document.addEventListener('toggle', function (e) {
                const t = e.target;
                if (!t.matches('details.gls-address-details[name]') || !t.open) return;
                document.querySelectorAll('details.gls-address-details[name="' + t.getAttribute('name') + '"]').forEach(function (d) {
                    if (d !== t && d.open) d.open = false;
                });
            }, true);
        })();
    </script>
@endonce
