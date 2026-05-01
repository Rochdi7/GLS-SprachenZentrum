@extends('layouts.main')

@section('title', 'Demande d\'attestation #' . $request->id)
@section('breadcrumb-item', 'Ecole')
@section('breadcrumb-item-link', route('backoffice.attestation_requests.index'))
@section('breadcrumb-item-active', 'Demande #' . $request->id)

@section('css')
    <link rel="stylesheet" href="{{ URL::asset('build/css/plugins/style.css') }}">
@endsection

@section('content')
    @if (session('success') || session('error'))
        <div class="alert alert-{{ session('error') ? 'danger' : 'success' }} alert-dismissible fade show" role="alert">
            {{ session('success') ?? session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        Demande #{{ $request->id }}
                        @if ($request->status === 'pending')
                            <span class="badge bg-light-warning text-warning ms-2">En attente</span>
                        @elseif ($request->status === 'accepted')
                            <span class="badge bg-light-success text-success ms-2">Acceptée</span>
                        @else
                            <span class="badge bg-light-danger text-danger ms-2">Refusée</span>
                        @endif
                    </h5>
                    <small class="text-muted">Reçue le {{ $request->created_at?->format('d/m/Y à H:i') }}</small>
                </div>

                <div class="card-body">
                    <h6 class="fw-bold mb-3">Étudiant</h6>
                    <dl class="row mb-4">
                        <dt class="col-sm-4">Nom complet</dt>
                        <dd class="col-sm-8">{{ $request->last_name }} {{ $request->first_name }}</dd>

                        <dt class="col-sm-4">Email</dt>
                        <dd class="col-sm-8"><a href="mailto:{{ $request->email }}">{{ $request->email }}</a></dd>

                        <dt class="col-sm-4">Téléphone</dt>
                        <dd class="col-sm-8">{{ $request->phone ?: '—' }}</dd>

                        <dt class="col-sm-4">Date de naissance</dt>
                        <dd class="col-sm-8">{{ $request->birth_date?->format('d/m/Y') ?: '—' }}</dd>

                        <dt class="col-sm-4">Lieu de naissance</dt>
                        <dd class="col-sm-8">{{ $request->birth_place ?: '—' }}</dd>
                    </dl>

                    <h6 class="fw-bold mb-3">Cours demandé</h6>
                    <dl class="row mb-4">
                        <dt class="col-sm-4">Groupe (saisi)</dt>
                        <dd class="col-sm-8"><em>{{ $request->group_name }}</em></dd>

                        <dt class="col-sm-4">Niveau</dt>
                        <dd class="col-sm-8"><span class="badge bg-light-primary text-primary">{{ $request->level }}</span></dd>

                        <dt class="col-sm-4">Langue souhaitée</dt>
                        <dd class="col-sm-8">{{ $request->language }}</dd>
                    </dl>

                    @if ($request->status !== 'pending')
                        <h6 class="fw-bold mb-3">Traitement</h6>
                        <dl class="row mb-0">
                            <dt class="col-sm-4">Examinée par</dt>
                            <dd class="col-sm-8">{{ $request->reviewer?->name ?? '—' }}</dd>

                            <dt class="col-sm-4">Examinée le</dt>
                            <dd class="col-sm-8">{{ $request->reviewed_at?->format('d/m/Y H:i') ?? '—' }}</dd>

                            @if ($request->status === 'refused')
                                <dt class="col-sm-4">Motif du refus</dt>
                                <dd class="col-sm-8">{{ $request->refusal_reason }}</dd>
                            @endif

                            @if ($request->status === 'accepted' && $request->attestation_id)
                                <dt class="col-sm-4">Attestation créée</dt>
                                <dd class="col-sm-8">
                                    <a href="{{ route('backoffice.attestations.edit', $request->attestation_id) }}">
                                        Voir l'attestation #{{ $request->attestation_id }}
                                    </a>
                                </dd>
                            @endif
                        </dl>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            @if ($request->status === 'pending')
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Actions</h6>
                    </div>
                    <div class="card-body">

                        @can('attestations.create')
                            <form action="{{ route('backoffice.attestation_requests.accept', $request->id) }}" method="POST" class="mb-3">
                                @csrf
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="ti ti-check me-1"></i> Accepter et créer l'attestation
                                </button>
                                <small class="text-muted d-block mt-1">
                                    Vous serez redirigé vers le formulaire de création, pré-rempli avec les informations de l'étudiant.
                                </small>
                            </form>
                        @endcan

                        @can('attestations.edit')
                            <hr>
                            <form action="{{ route('backoffice.attestation_requests.refuse', $request->id) }}" method="POST">
                                @csrf
                                <label class="form-label fw-bold">Motif du refus <span class="text-danger">*</span></label>
                                <textarea name="refusal_reason" class="form-control mb-2" rows="4" required minlength="5"
                                          placeholder="Expliquez à l'étudiant pourquoi sa demande est refusée…">{{ old('refusal_reason') }}</textarea>
                                @error('refusal_reason')
                                    <small class="text-danger d-block mb-2">{{ $message }}</small>
                                @enderror
                                <button type="submit" class="btn btn-danger w-100"
                                        onclick="return confirm('Confirmer le refus ? L\'étudiant sera notifié par email.')">
                                    <i class="ti ti-x me-1"></i> Refuser la demande
                                </button>
                            </form>
                        @endcan

                    </div>
                </div>
            @else
                <div class="card">
                    <div class="card-body text-center text-muted">
                        Cette demande a déjà été traitée.
                    </div>
                </div>
            @endif

            <div class="mt-3">
                <a href="{{ route('backoffice.attestation_requests.index') }}" class="btn btn-link">
                    <i class="ti ti-arrow-left me-1"></i> Retour à la liste
                </a>
            </div>
        </div>
    </div>
@endsection
