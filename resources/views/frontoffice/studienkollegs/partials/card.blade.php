<div class="gls-card h-100">

    <div class="gls-card-image position-relative">
        <img src="{{ asset('assets/images/placeholder-campus.webp') }}"
             alt="Studienkolleg"
             class="img-fluid rounded">

        <span class="badge bg-warning text-dark position-absolute top-0 end-0 m-2">
            Premium
        </span>
    </div>

    <div class="gls-card-body mt-3">
        <h5 class="mb-1">Studienkolleg Leipzig</h5>

        <p class="text-muted mb-2">
            University of Leipzig – Germany
        </p>

        <ul class="list-unstyled small mb-3">
            <li>⏱ 2 Semesters</li>
            <li>💶 Free Tuition</li>
            <li>📘 T / M Course</li>
        </ul>

        <a href="{{ route('front.studienkollegs.show') }}"
           class="btn btn-outline-primary w-100">
            View details
        </a>
    </div>

</div>
