@extends('layouts.main')

@section('title', 'Avis & Feedbacks')
@section('breadcrumb-item', 'Ecole')
@section('breadcrumb-item-active', 'Avis & Feedbacks')

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
        <div class="col-12">
            <div class="card table-card">
                <div class="card-header">
                    <div class="d-sm-flex align-items-center justify-content-between">
                        <h5 class="mb-3 mb-sm-0">Avis & Feedbacks étudiants</h5>

                        <div class="d-flex flex-wrap gap-2">
                            <div class="btn-group" role="group" aria-label="Filtre lecture">
                                <a href="{{ route('backoffice.feedbacks.index') }}"
                                   class="btn btn-sm {{ !$currentFilter ? 'btn-primary' : 'btn-outline-secondary' }}">
                                    Tous <span class="badge bg-light text-dark ms-1">{{ $counts['all'] }}</span>
                                </a>
                                <a href="{{ route('backoffice.feedbacks.index', ['filter' => 'unread']) }}"
                                   class="btn btn-sm {{ $currentFilter === 'unread' ? 'btn-warning' : 'btn-outline-warning' }}">
                                    Non lus <span class="badge bg-light text-dark ms-1">{{ $counts['unread'] }}</span>
                                </a>
                                <a href="{{ route('backoffice.feedbacks.index', ['filter' => 'read']) }}"
                                   class="btn btn-sm {{ $currentFilter === 'read' ? 'btn-success' : 'btn-outline-success' }}">
                                    Lus <span class="badge bg-light text-dark ms-1">{{ $counts['read'] }}</span>
                                </a>
                            </div>

                            <a href="{{ route('backoffice.feedbacks.qr') }}" class="btn btn-sm btn-dark">
                                <i class="ti ti-qrcode me-1"></i> QR code à partager
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body pt-3">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Étudiant</th>
                                    <th>Centre</th>
                                    <th>Message</th>
                                    <th>Statut</th>
                                    <th>Reçu le</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($feedbacks as $f)
                                    <tr class="{{ !$f->is_read ? 'table-warning' : '' }}">
                                        <td>{{ $f->id }}</td>
                                        <td>
                                            <strong>{{ $f->full_name }}</strong>
                                        </td>
                                        <td>
                                            @if ($f->site)
                                                {{ $f->site->name }}@if($f->site->city) <small class="text-muted">— {{ $f->site->city }}</small>@endif
                                            @elseif ($f->site_name_snapshot)
                                                <em>{{ $f->site_name_snapshot }}</em>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span style="display:inline-block;max-width:320px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;vertical-align:middle;"
                                                  title="{{ $f->message }}">
                                                {{ \Illuminate\Support\Str::limit($f->message, 80) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if ($f->is_read)
                                                <span class="badge bg-light-success text-success">Lu</span>
                                            @else
                                                <span class="badge bg-light-warning text-warning">Non lu</span>
                                            @endif
                                        </td>
                                        <td>{{ $f->created_at?->format('d/m/Y H:i') }}</td>
                                        <td>
                                            <a href="{{ route('backoffice.feedbacks.show', $f->id) }}"
                                               class="avtar avtar-xs btn-link-secondary me-2" title="Voir">
                                                <i class="ti ti-eye f-20"></i>
                                            </a>
                                            <form action="{{ route('backoffice.feedbacks.destroy', $f->id) }}" method="POST" class="d-inline-block">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="avtar avtar-xs btn-link-secondary border-0 bg-transparent p-0"
                                                        onclick="return confirm('Supprimer cet avis ?')"
                                                        title="Supprimer">
                                                    <i class="ti ti-trash f-20"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">Aucun avis pour ce filtre.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
