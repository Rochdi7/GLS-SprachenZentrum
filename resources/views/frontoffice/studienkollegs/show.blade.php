@extends('frontoffice.layouts.app')

@section('title', 'Studienkolleg Details')

@section('content')

<section class="py-5">
    <div class="container">

        <a href="{{ route('front.studienkollegs') }}" class="btn btn-link mb-4">
            ← Back to Studienkollegs
        </a>

        <h1 class="mb-3">Studienkolleg Leipzig</h1>

        <p class="text-muted">
            Public Studienkolleg – Germany
        </p>

        <hr>

        <h5>Available Courses</h5>
        <ul>
            <li>T Course – Technical</li>
            <li>M Course – Medical</li>
        </ul>

        <h5 class="mt-4">Duration</h5>
        <p>2 Semesters</p>

        <h5>Tuition Fees</h5>
        <p>Free</p>

        <h5>Requirements</h5>
        <ul>
            <li>B1 / B2 German</li>
            <li>Secondary School Certificate</li>
        </ul>

    </div>
</section>

@endsection
