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

            {{-- HEADER --}}
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    Certificat – {{ $certificate->full_name }}
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

                {{-- =============================== --}}
                {{--         PERSONAL INFO           --}}
                {{-- =============================== --}}
                <h5 class="fw-bold mb-3">Informations personnelles</h5>

                <div class="row mb-4">

                    <div class="col-md-4">
                        <p class="mb-1 fw-bold">Nom :</p>
                        <p>{{ $certificate->last_name }}</p>
                    </div>

                    <div class="col-md-4">
                        <p class="mb-1 fw-bold">Prénom :</p>
                        <p>{{ $certificate->first_name }}</p>
                    </div>

                    <div class="col-md-4">
                        <p class="mb-1 fw-bold">Date de naissance :</p>
                        <p>{{ $certificate->birth_date->format('d/m/Y') }}</p>
                    </div>

                    <div class="col-md-6">
                        <p class="mb-1 fw-bold">Lieu de naissance :</p>
                        <p>{{ $certificate->birth_place }}</p>
                    </div>

                </div>

                <hr>


                {{-- =============================== --}}
                {{--          EXAM META              --}}
                {{-- =============================== --}}
                <h5 class="fw-bold mb-3">Détails de l'examen</h5>

                <div class="row mb-4">

                    <div class="col-md-4">
                        <p class="mb-1 fw-bold">Niveau :</p>
                        <span class="badge bg-light-primary text-primary fs-6 px-3">
                            {{ $certificate->exam_level }}
                        </span>
                    </div>

                    <div class="col-md-4">
                        <p class="mb-1 fw-bold">Date d'examen :</p>
                        <p>{{ $certificate->exam_date->format('d/m/Y') }}</p>
                    </div>

                    <div class="col-md-4">
                        <p class="mb-1 fw-bold">Date de délivrance :</p>
                        <p>{{ $certificate->issue_date->format('d/m/Y') }}</p>
                    </div>

                    <div class="col-md-6">
                        <p class="mb-1 fw-bold">Numéro du certificat :</p>
                        <p>{{ $certificate->certificate_number }}</p>
                    </div>

                </div>

                <hr>


                {{-- =============================== --}}
                {{--        WRITTEN EXAM (Écrit)     --}}
                {{-- =============================== --}}
                <h5 class="fw-bold mb-3">Schriftliche Prüfung (Écrit)</h5>

                <table class="table table-bordered mb-4">
                    <thead>
                        <tr>
                            <th>Section</th>
                            <th>Score</th>
                            <th>Max</th>
                        </tr>
                    </thead>
                    <tbody>

                        {{-- Total Written --}}
                        <tr>
                            <td><strong>Total Écrit</strong></td>
                            <td>{{ $certificate->written_total }}</td>
                            <td>{{ $certificate->written_max }}</td>
                        </tr>

                        {{-- Reading --}}
                        <tr>
                            <td>Leseverstehen</td>
                            <td>{{ $certificate->reading_score }}</td>
                            <td>{{ $certificate->reading_max }}</td>
                        </tr>

                        {{-- Grammar --}}
                        <tr>
                            <td>Sprachbausteine</td>
                            <td>{{ $certificate->grammar_score }}</td>
                            <td>{{ $certificate->grammar_max }}</td>
                        </tr>

                        {{-- Listening --}}
                        <tr>
                            <td>Hörverstehen</td>
                            <td>{{ $certificate->listening_score }}</td>
                            <td>{{ $certificate->listening_max }}</td>
                        </tr>

                        {{-- Writing --}}
                        <tr>
                            <td>Schriftlicher Ausdruck</td>
                            <td>{{ $certificate->writing_score }}</td>
                            <td>{{ $certificate->writing_max }}</td>
                        </tr>

                    </tbody>
                </table>

                <hr>


                {{-- =============================== --}}
                {{--        ORAL EXAM (Oral)         --}}
                {{-- =============================== --}}
                <h5 class="fw-bold mb-3">Mündliche Prüfung (Oral)</h5>

                <table class="table table-bordered mb-4">
                    <thead>
                        <tr>
                            <th>Section</th>
                            <th>Score</th>
                            <th>Max</th>
                        </tr>
                    </thead>
                    <tbody>

                        {{-- Total Oral --}}
                        <tr>
                            <td><strong>Total Oral</strong></td>
                            <td>{{ $certificate->oral_total }}</td>
                            <td>{{ $certificate->oral_max }}</td>
                        </tr>

                        {{-- Presentation --}}
                        <tr>
                            <td>Präsentation</td>
                            <td>{{ $certificate->presentation_score }}</td>
                            <td>{{ $certificate->presentation_max }}</td>
                        </tr>

                        {{-- Discussion --}}
                        <tr>
                            <td>Diskussion</td>
                            <td>{{ $certificate->discussion_score }}</td>
                            <td>{{ $certificate->discussion_max }}</td>
                        </tr>

                        {{-- Problem Solving --}}
                        <tr>
                            <td>Problemlösung</td>
                            <td>{{ $certificate->problemsolving_score }}</td>
                            <td>{{ $certificate->problemsolving_max }}</td>
                        </tr>

                    </tbody>
                </table>

                <hr>


                {{-- =============================== --}}
                {{--          FINAL RESULT           --}}
                {{-- =============================== --}}
                <h5 class="fw-bold mb-3">Résultat final</h5>

                @php
                    $isSuccess = str_contains(strtolower($certificate->final_result), 'réussi')
                                || str_contains(strtolower($certificate->final_result), 'success')
                                || str_contains(strtolower($certificate->final_result), 'gut')
                                || str_contains(strtolower($certificate->final_result), 'befriedigend');
                @endphp

                @if($isSuccess)
                    <span class="badge bg-success text-white p-2 fs-6">
                        {{ $certificate->final_result }}
                    </span>
                @else
                    <span class="badge bg-danger text-white p-2 fs-6">
                        {{ $certificate->final_result }}
                    </span>
                @endif

            </div>

        </div>

    </div>
</div>

@endsection
