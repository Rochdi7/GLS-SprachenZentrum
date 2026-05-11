@extends('layouts.main')

@section('title', 'Détails Certificat')
@section('breadcrumb-item', 'Examens')
@section('breadcrumb-item-link', route('backoffice.certificates.index'))
@section('breadcrumb-item-active', 'Certificat #' . $certificate->id)

@section('css')
    <link rel="stylesheet" href="{{ URL::asset('build/css/plugins/style.css') }}">
@endsection

@section('content')

    @php
        // URL publique du certificat (utilisée uniquement pour le QR et le lien)
        $publicUrl = route('certificates.public.download', ['token' => $certificate->public_token]);
    @endphp

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

                        {{-- Lien public (optionnel, admin only) --}}
                        <a href="{{ $publicUrl }}" target="_blank" class="btn btn-outline-dark me-2">
                            Lien public
                        </a>

                        <a href="{{ route('backoffice.certificates.pdf', $certificate->id) }}" class="btn btn-primary">
                            Export PDF
                        </a>
                    </div>
                </div>


                <div class="card-body">

                    {{-- ===============================
                       PERSONAL INFO
                    =============================== --}}
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

                    {{-- ===============================
                       EXAM META
                    =============================== --}}
                    <h5 class="fw-bold mb-3">Détails de l'examen</h5>

                    <div class="row mb-4 align-items-start">

                        {{-- LEFT --}}
                        <div class="col-md-8">
                            <div class="row">

                                <div class="col-md-4">
                                    <p class="mb-1 fw-bold">Type / Niveau :</p>
                                    <span class="badge bg-light-primary text-primary fs-6 px-3">
                                        {{ strtoupper($certificate->certificate_type) }} — {{ $certificate->exam_level }}
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

                                {{-- public_token is intentionally hidden --}}
                            </div>
                        </div>

                        {{-- RIGHT : QR --}}
                        <div class="col-md-4 text-center">
                            <div class="border rounded p-3">
                                @if ($certificate->public_token)
                                    <img
                                        src="https://api.qrserver.com/v1/create-qr-code/?size=220x220&data={{ urlencode($publicUrl) }}"
                                        alt="QR Code Certificat"
                                        class="img-fluid"
                                    >
                                    <div class="text-muted mt-2" style="font-size: 13px;">
                                        Scanner pour télécharger<br>le certificat
                                    </div>
                                @else
                                    <div class="alert alert-warning mb-0">
                                        QR code indisponible.
                                    </div>
                                @endif
                            </div>
                        </div>

                    </div>

                    <hr>

                    @if($certificate->isA2())
                        {{-- ===============================
                           A2 SCORES
                        =============================== --}}
                        <h5 class="fw-bold mb-3">Prüfungsergebnisse — A2</h5>

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
                                    <td><strong>Gesamt</strong></td>
                                    <td><strong>{{ $certificate->total_score }}</strong></td>
                                    <td><strong>{{ $certificate->total_max }}</strong></td>
                                </tr>
                                <tr>
                                    <td>Lesen</td>
                                    <td>{{ $certificate->reading_score }}</td>
                                    <td>{{ $certificate->reading_max }}</td>
                                </tr>
                                <tr>
                                    <td>Hören</td>
                                    <td>{{ $certificate->listening_score }}</td>
                                    <td>{{ $certificate->listening_max }}</td>
                                </tr>
                                <tr>
                                    <td>Schreiben</td>
                                    <td>{{ $certificate->writing_score }}</td>
                                    <td>{{ $certificate->writing_max }}</td>
                                </tr>
                                <tr>
                                    <td>Sprechen</td>
                                    <td>{{ $certificate->speaking_score }}</td>
                                    <td>{{ $certificate->speaking_max }}</td>
                                </tr>
                            </tbody>
                        </table>
                    @else
                        {{-- ===============================
                           B2 WRITTEN EXAM
                        =============================== --}}
                        <h5 class="fw-bold mb-3">Schriftliche Prüfung (Écrit) — B2</h5>

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
                                    <td><strong>{{ $certificate->written_total }}</strong></td>
                                    <td><strong>{{ $certificate->written_max }}</strong></td>
                                </tr>
                                <tr>
                                    <td>Leseverstehen</td>
                                    <td>{{ $certificate->reading_score }}</td>
                                    <td>{{ $certificate->reading_max }}</td>
                                </tr>
                                <tr>
                                    <td>Sprachbausteine</td>
                                    <td>{{ $certificate->grammar_score }}</td>
                                    <td>{{ $certificate->grammar_max }}</td>
                                </tr>
                                <tr>
                                    <td>Hörverstehen</td>
                                    <td>{{ $certificate->listening_score }}</td>
                                    <td>{{ $certificate->listening_max }}</td>
                                </tr>
                                <tr>
                                    <td>Schriftlicher Ausdruck</td>
                                    <td>{{ $certificate->writing_score }}</td>
                                    <td>{{ $certificate->writing_max }}</td>
                                </tr>
                            </tbody>
                        </table>

                        <hr>

                        {{-- ===============================
                           B2 ORAL EXAM
                        =============================== --}}
                        <h5 class="fw-bold mb-3">Mündliche Prüfung (Oral) — B2</h5>

                        <table class="table table-bordered mb-4">
                            <tbody>
                                <tr>
                                    <td><strong>Total Oral</strong></td>
                                    <td><strong>{{ $certificate->oral_total }}</strong></td>
                                    <td><strong>{{ $certificate->oral_max }}</strong></td>
                                </tr>
                                <tr>
                                    <td>Präsentation</td>
                                    <td>{{ $certificate->presentation_score }}</td>
                                    <td>{{ $certificate->presentation_max }}</td>
                                </tr>
                                <tr>
                                    <td>Diskussion</td>
                                    <td>{{ $certificate->discussion_score }}</td>
                                    <td>{{ $certificate->discussion_max }}</td>
                                </tr>
                                <tr>
                                    <td>Problemlösung</td>
                                    <td>{{ $certificate->problemsolving_score }}</td>
                                    <td>{{ $certificate->problemsolving_max }}</td>
                                </tr>
                            </tbody>
                        </table>
                    @endif

                    <hr>

                    {{-- ===============================
                       FINAL RESULT
                    =============================== --}}
                    <h5 class="fw-bold mb-3">Résultat final</h5>

                    @php
                        $isSuccess =
                            str_contains(strtolower($certificate->final_result), 'réussi') ||
                            str_contains(strtolower($certificate->final_result), 'success') ||
                            str_contains(strtolower($certificate->final_result), 'gut') ||
                            str_contains(strtolower($certificate->final_result), 'befriedigend');
                    @endphp

                    <span class="badge {{ $isSuccess ? 'bg-success' : 'bg-danger' }} text-white p-2 fs-6">
                        {{ $certificate->final_result }}
                    </span>

                </div>

            </div>

        </div>
    </div>

@endsection
