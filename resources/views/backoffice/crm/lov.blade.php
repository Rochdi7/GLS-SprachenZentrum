@extends('layouts.main')

@section('title', 'CRM — LOV ' . $kind)
@section('breadcrumb-item', 'CRM')
@section('breadcrumb-item-active', 'LOV: ' . $kind)

@section('content')
    @include('backoffice.crm.partials._center')

    <form method="GET" class="card mb-3">
        <div class="card-body py-3">
            <div class="row g-2 align-items-end">
                <div class="col-12 col-sm-3">
                    <label class="form-label small text-muted mb-1">Limit</label>
                    <input type="number" name="limit" value="{{ $limit }}" class="form-control form-control-sm">
                </div>
                <div class="col-12 col-sm-auto">
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="ti ti-search me-1"></i> Reload
                    </button>
                </div>
                <div class="col">
                    <div class="small text-muted">
                        Endpoint <code>/api/external/v1/lov/{{ $kind }}</code>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <div class="card"><div class="card-body">
        @include('backoffice.crm.partials._table')
    </div></div>
@endsection
