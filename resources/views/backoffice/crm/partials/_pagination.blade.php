{{--
    Numbered pagination for CRM tables.

    Expects:
      $pagination : array — from the API ('page' 0-based, 'totalPages', 'hasNext'/'hasMore')
--}}

@php
    $page       = (int) ($pagination['page'] ?? 0);            // 0-based from API
    $totalPages = (int) ($pagination['totalPages'] ?? 0);
    $hasNext    = (bool) ($pagination['hasNext'] ?? $pagination['hasMore'] ?? false);
    $params     = request()->query();

    $linkFor = function (int $p) use ($params) {
        return '?' . http_build_query(array_merge($params, ['page' => $p]));
    };

    // Build the windowed list of page numbers to show.
    // Always show first + last; window of 2 around current; ellipsis if gaps.
    $current = $page; // 0-based
    $last    = max(0, $totalPages - 1);

    $window = [];
    if ($totalPages > 0) {
        $candidates = array_unique([
            0, 1,
            $current - 1, $current, $current + 1,
            $last - 1, $last,
        ]);
        sort($candidates);
        foreach ($candidates as $p) {
            if ($p < 0 || $p > $last) continue;
            // Insert ellipsis if there is a gap
            if (!empty($window) && $p > end($window) + 1) {
                $window[] = '...';
            }
            $window[] = $p;
        }
    }
@endphp

@if($totalPages > 1)
<nav aria-label="Pagination" class="mt-3">
    <ul class="pagination pagination-sm mb-0">
        {{-- Previous --}}
        <li class="page-item {{ $page <= 0 ? 'disabled' : '' }}">
            @if($page > 0)
                <a class="page-link" href="{{ $linkFor($page - 1) }}" aria-label="Précédent">
                    <i class="ti ti-chevron-left"></i> Précédent
                </a>
            @else
                <span class="page-link"><i class="ti ti-chevron-left"></i> Précédent</span>
            @endif
        </li>

        {{-- Numbered pages --}}
        @foreach($window as $p)
            @if($p === '...')
                <li class="page-item disabled"><span class="page-link">…</span></li>
            @elseif($p === $current)
                <li class="page-item active" aria-current="page">
                    <span class="page-link">{{ $p + 1 }}</span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link" href="{{ $linkFor($p) }}">{{ $p + 1 }}</a>
                </li>
            @endif
        @endforeach

        {{-- Next --}}
        <li class="page-item {{ !$hasNext ? 'disabled' : '' }}">
            @if($hasNext)
                <a class="page-link" href="{{ $linkFor($page + 1) }}" aria-label="Suivant">
                    Suivant <i class="ti ti-chevron-right"></i>
                </a>
            @else
                <span class="page-link">Suivant <i class="ti ti-chevron-right"></i></span>
            @endif
        </li>
    </ul>
</nav>
@elseif($hasNext || $page > 0)
    {{-- Fallback when totalPages is not provided (includeTotal=false) --}}
    <nav aria-label="Pagination" class="mt-3">
        <ul class="pagination pagination-sm mb-0">
            <li class="page-item {{ $page <= 0 ? 'disabled' : '' }}">
                @if($page > 0)
                    <a class="page-link" href="{{ $linkFor($page - 1) }}"><i class="ti ti-chevron-left"></i> Précédent</a>
                @else
                    <span class="page-link"><i class="ti ti-chevron-left"></i> Précédent</span>
                @endif
            </li>
            <li class="page-item active"><span class="page-link">{{ $page + 1 }}</span></li>
            <li class="page-item {{ !$hasNext ? 'disabled' : '' }}">
                @if($hasNext)
                    <a class="page-link" href="{{ $linkFor($page + 1) }}">Suivant <i class="ti ti-chevron-right"></i></a>
                @else
                    <span class="page-link">Suivant <i class="ti ti-chevron-right"></i></span>
                @endif
            </li>
        </ul>
    </nav>
@endif
