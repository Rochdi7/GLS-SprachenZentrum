@extends('layouts.main')

@section('title', 'Détails Certificat')
@section('breadcrumb-item', 'Examens')
@section('breadcrumb-item-active', 'Certificat #' . $certificate->id)

@section('css')
<link rel="stylesheet" href="{{ URL::asset('build/css/plugins/style.css') }}">
@endsection

@section('content')

<div class="row">
    <div class="col-md-12">

        <div class="card">

            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    Certificat – {{ $certificate->last_name }} {{ $certificate->first_name }}
                </h5>

                <div>
                    <a href="{{ route('backoffice.certificates.index') }}" class="btn btn-secondary me-2">
                        Retour
                    </a>

                    <a href="{{ route('backoffice.certificates.pdf', $certificate->id) }}" 
                       class="btn btn-primary">
                        Export PDF
                    </a>
                </div>
            </div>

            <div class="card-body">

                {{-- PERSONAL INFO --}}
                <h5 class="fw-bold mb-3">Informations personnelles</h5>
                <div class="row mb-4">

                    <div class="col-md-4">
                        <p class="mb-1"><strong>Nom :</strong></p>
                        <p>{{ $certificate->last_name }}</p>
                    </div>

                    <div class="col-md-4">
                        <p class="mb-1"><strong>Prénom :</strong></p>
                        <p>{{ $certificate->first_name }}</p>
                    </div>

                    <div class="col-md-4">
                        <p class="mb-1"><strong>Date de naissance :</strong></p>
                        <p>{{ $certificate->birth_date->format('Y-m-d') }}</p>
                    </div>

                    <div class="col-md-6">
                        <p class="mb-1"><strong>Lieu de naissance :</strong></p>
                        <p>{{ $certificate->birth_place }}</p>
                    </div>

                </div>

                <hr>

                {{-- EXAM META --}}
                <h5 class="fw-bold mb-3">Détails de l'examen</h5>
                <div class="row mb-4">

                    <div class="col-md-4">
                        <p class="mb-1"><strong>Niveau :</strong></p>
                        <span class="badge bg-light-primary text-primary">
                            {{ $certificate->exam_level }}
                        </span>
                    </div>

                    <div class="col-md-4">
                        <p class="mb-1"><strong>Date d'examen :</strong></p>
                        <p>{{ $certificate->exam_date->format('Y-m-d') }}</p>
                    </div>

                    <div class="col-md-4">
                        <p class="mb-1"><strong>Date de délivrance :</strong></p>
                        <p>{{ $certificate->issue_date->format('Y-m-d') }}</p>
                    </div>

                    <div class="col-md-6">
                        <p class="mb-1"><strong>Numéro du certificat :</strong></p>
                        <p>{{ $certificate->certificate_number }}</p>
                    </div>

                </div>

                <hr>

                {{-- WRITTEN EXAM --}}
                <h5 class="fw-bold mb-3">Schriftliche Prüfung (Écrit)</h5>

                @php
                    $READING_MAX = 75;
                    $GRAMMAR_MAX = 30;
                    $LISTENING_MAX = 75;
                    $WRITING_MAX = 45;

                    $WRITTEN_MAX = 225;
                @endphp

                <table class="table table-bordered mb-4">
                    <thead>
                        <tr>
                            <th>Section</th>
                            <th>Score</th>
                            <th>Max</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Total Écrit</strong></td>
                            <td>{{ $certificate->written_total }}</td>
                            <td>{{ $WRITTEN_MAX }}</td>
                        </tr>
                        <tr>
                            <td>Leseverstehen</td>
                            <td>{{ $certificate->reading_score }}</td>
                            <td>{{ $READING_MAX }}</td>
                        </tr>
                        <tr>
                            <td>Sprachbausteine</td>
                            <td>{{ $certificate->grammar_score }}</td>
                            <td>{{ $GRAMMAR_MAX }}</td>
                        </tr>
                        <tr>
                            <td>Hörverstehen</td>
                            <td>{{ $certificate->listening_score }}</td>
                            <td>{{ $LISTENING_MAX }}</td>
                        </tr>
                        <tr>
                            <td>Schriftlicher Ausdruck</td>
                            <td>{{ $certificate->writing_score }}</td>
                            <td>{{ $WRITING_MAX }}</td>
                        </tr>
                    </tbody>
                </table>

                <hr>

                {{-- ORAL EXAM --}}
                <h5 class="fw-bold mb-3">Mündliche Prüfung (Oral)</h5>

                @php
                    $PRESENTATION_MAX = 25;
                    $DISCUSSION_MAX = 25;
                    $PROBLEM_MAX = 25;
                    $ORAL_MAX = 75;
                @endphp

                <table class="table table-bordered mb-4">
                    <thead>
                        <tr>
                            <th>Section</th>
                            <th>Score</th>
                            <th>Max</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Total Oral</strong></td>
                            <td>{{ $certificate->oral_total }}</td>
                            <td>{{ $ORAL_MAX }}</td>
                        </tr>
                        <tr>
                            <td>Präsentation</td>
                            <td>{{ $certificate->presentation_score }}</td>
                            <td>{{ $PRESENTATION_MAX }}</td>
                        </tr>
                        <tr>
                            <td>Diskussion</td>
                            <td>{{ $certificate->discussion_score }}</td>
                            <td>{{ $DISCUSSION_MAX }}</td>
                        </tr>
                        <tr>
                            <td>Problemlösung</td>
                            <td>{{ $certificate->problemsolving_score }}</td>
                            <td>{{ $PROBLEM_MAX }}</td>
                        </tr>
                    </tbody>
                </table>

                <hr>

                {{-- FINAL RESULT --}}
                <h5 class="fw-bold mb-3">Résultat Final</h5>

                @if(Str::contains(strtolower($certificate->final_result), 'réussi'))
                    <span class="badge bg-success text-white p-2 fs-6">{{ $certificate->final_result }}</span>
                @else
                    <span class="badge bg-danger text-white p-2 fs-6">{{ $certificate->final_result }}</span>
                @endif

            </div>

        </div>

    </div>
</div>

@endsection
