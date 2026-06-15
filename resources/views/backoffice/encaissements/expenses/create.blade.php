@extends('layouts.main')

@section('title', 'Nouvelle charge')
@section('breadcrumb-item', 'Charges')
@section('breadcrumb-item-active', 'Nouvelle charge')

@section('content')

    <div class="row">
        <div class="col-lg-10">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="mb-0">Nouvelle charge</h5>
                        <a href="{{ route('backoffice.encaissements.expenses.index') }}" class="btn btn-outline-secondary">
                            <i class="ph-duotone ph-arrow-left me-1"></i> Retour
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                        </div>
                    @endif

                    <form action="{{ route('backoffice.encaissements.expenses.store') }}" method="POST">
                        @csrf
                        <div class="row g-3">

                            {{-- Centre --}}
                            <div class="col-md-6">
                                <label class="form-label">Centre <span class="text-danger">*</span></label>
                                <select name="site_id" class="form-select" required>
                                    <option value="">-- Choisir --</option>
                                    @foreach($sites as $s)
                                        <option value="{{ $s->id }}" {{ old('site_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Type --}}
                            <div class="col-md-6">
                                <label class="form-label">Type <span class="text-danger">*</span></label>
                                <select name="type" class="form-select" required>
                                    <option value="">-- Choisir --</option>
                                    @foreach(\App\Models\SiteExpense::TYPES as $val => $label)
                                        <option value="{{ $val }}" {{ old('type') == $val ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Libellé --}}
                            <div class="col-md-6">
                                <label class="form-label">Libellé <span class="text-danger">*</span></label>
                                <input type="text" name="label" class="form-control" value="{{ old('label') }}" required>
                            </div>

                            {{-- Montant --}}
                            <div class="col-md-6">
                                <label class="form-label">Montant (MAD) <span class="text-danger">*</span></label>
                                <input type="number" name="amount" class="form-control" value="{{ old('amount') }}" step="0.01" min="0" required>
                            </div>

                            {{-- Mois --}}
                            <div class="col-md-6">
                                <label class="form-label">Mois <span class="text-danger">*</span></label>
                                <input type="date" name="month" class="form-control" value="{{ old('month') }}" required>
                            </div>

                            {{-- Notes --}}
                            <div class="col-12">
                                <label class="form-label">Notes</label>
                                <textarea name="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
                            </div>

                        </div>
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="ph-duotone ph-check me-1"></i> Enregistrer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection
