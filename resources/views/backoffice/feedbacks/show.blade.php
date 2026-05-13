@extends('layouts.main')

@section('title', 'Avis #' . $feedback->id)
@section('breadcrumb-item', 'Ecole')
@section('breadcrumb-item-link', route('backoffice.feedbacks.index'))
@section('breadcrumb-item-active', 'Avis #' . $feedback->id)

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
                        Avis #{{ $feedback->id }}
                        @if ($feedback->is_read)
                            <span class="badge bg-light-success text-success ms-2">Lu</span>
                        @else
                            <span class="badge bg-light-warning text-warning ms-2">Non lu</span>
                        @endif
                    </h5>
                    <small class="text-muted">Reçu le {{ $feedback->created_at?->format('d/m/Y à H:i') }}</small>
                </div>

                <div class="card-body">
                    <h6 class="fw-bold mb-3">Étudiant</h6>
                    <dl class="row mb-4">
                        <dt class="col-sm-4">Nom complet</dt>
                        <dd class="col-sm-8">{{ $feedback->full_name }}</dd>

                        <dt class="col-sm-4">Centre GLS</dt>
                        <dd class="col-sm-8">
                            @if ($feedback->site)
                                {{ $feedback->site->name }}@if($feedback->site->city) — {{ $feedback->site->city }}@endif
                            @elseif ($feedback->site_name_snapshot)
                                <em>{{ $feedback->site_name_snapshot }}</em> <small class="text-muted">(centre supprimé)</small>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </dd>
                    </dl>

                    <h6 class="fw-bold mb-3">Message</h6>
                    <div class="p-3 rounded" style="background:#f8f9fa;white-space:pre-wrap;line-height:1.6;">{{ $feedback->message }}</div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            @can('feedbacks.delete')
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Actions</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('backoffice.feedbacks.destroy', $feedback->id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger w-100"
                                onclick="return confirm('Supprimer cet avis ?')">
                            <i class="ti ti-trash me-1"></i> Supprimer l'avis
                        </button>
                    </form>
                </div>
            </div>
            @endcan

            <div class="mt-3">
                <a href="{{ route('backoffice.feedbacks.index') }}" class="btn btn-link">
                    <i class="ti ti-arrow-left me-1"></i> Retour à la liste
                </a>
            </div>
        </div>
    </div>
@endsection
