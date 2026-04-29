@extends('layouts.main')

@section('title', 'Modifier import présence v' . $import->version)
@section('breadcrumb-item', 'Paiement Professeurs')
@section('breadcrumb-item-link', route('backoffice.payroll.presence.dashboard'))
@section('breadcrumb-item-active', 'Modifier v' . $import->version)

@section('content')

    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h5>Modifier l'import — {{ $group->name }} (v{{ $import->version }})</h5>
                    <p class="text-muted mb-0">
                        Vous pouvez ajuster les métadonnées sans réimporter le fichier.
                        Téléchargez un nouveau fichier uniquement si vous voulez remplacer les présences.
                    </p>
                </div>
                <div class="card-body">

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <form action="{{ route('backoffice.payroll.presence.import.update', ['group' => $group->id, 'import' => $import->id]) }}"
                          method="POST"
                          enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            {{-- Group (read-only — version belongs to one group) --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Groupe</label>
                                <input type="text" class="form-control" value="{{ $group->name }} — {{ $group->level }}" readonly disabled>
                            </div>

                            {{-- Professeur display --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Professeur</label>
                                <input type="text" class="form-control" value="{{ $group->teacher?->name ?? '—' }}" readonly disabled>
                            </div>

                            {{-- Month --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Mois <span class="text-danger">*</span></label>
                                <input type="month" name="month"
                                       class="form-control"
                                       value="{{ old('month', $import->month?->format('Y-m')) }}"
                                       required>
                                <small class="text-muted">Mois de référence</small>
                            </div>

                            {{-- Period --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Période <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="date" name="date_start"
                                           class="form-control"
                                           value="{{ old('date_start', $import->date_start?->format('Y-m-d')) }}"
                                           required>
                                    <span class="input-group-text">→</span>
                                    <input type="date" name="date_end"
                                           class="form-control"
                                           value="{{ old('date_end', $import->date_end?->format('Y-m-d')) }}"
                                           required>
                                </div>
                                <small class="text-muted">Période réelle couverte par la feuille</small>
                            </div>

                            {{-- Payment per student --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Taux par étudiant (DH)</label>
                                <input type="number" name="payment_per_student"
                                       class="form-control"
                                       value="{{ old('payment_per_student', $import->payment_per_student) }}"
                                       step="0.01" min="0"
                                       placeholder="Laisser vide = taux enseignant">
                                <small class="text-muted">
                                    Taux enseignant :
                                    <strong>{{ $group->teacher?->payment_per_student ? number_format($group->teacher->payment_per_student, 2) . ' DH' : '—' }}</strong>
                                </small>
                            </div>

                            {{-- Excel File (optional) --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">
                                    Remplacer le fichier Excel
                                    <span class="badge bg-light-secondary ms-1">optionnel</span>
                                </label>
                                <input type="file" name="file"
                                       class="form-control"
                                       accept=".xlsx,.xls,.csv">
                                <small class="text-muted">
                                    Fichier actuel :
                                    <strong>{{ $import->file_name ?? '—' }}</strong>.
                                    Ne rien sélectionner pour garder les présences existantes.
                                </small>
                            </div>

                            {{-- Notes --}}
                            <div class="col-md-12 mb-3">
                                <label class="form-label fw-bold">Notes (optionnel)</label>
                                <textarea name="notes" class="form-control" rows="3"
                                          placeholder="Notes sur cet import...">{{ old('notes', $import->notes) }}</textarea>
                            </div>
                        </div>

                        <div class="alert alert-warning mb-3">
                            <i class="ph-duotone ph-warning-circle me-1"></i>
                            <strong>Attention :</strong>
                            si vous téléchargez un nouveau fichier, les étudiants et présences existants
                            seront <strong>remplacés</strong> par les données du nouveau fichier.
                            Les montants hebdomadaires modifiés manuellement seront perdus.
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('backoffice.payroll.presence.import.show', ['group' => $group->id, 'import' => $import->id]) }}"
                               class="btn btn-outline-secondary">
                                Annuler
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="ph-duotone ph-floppy-disk me-1"></i> Enregistrer
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

@endsection
