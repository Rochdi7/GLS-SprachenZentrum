@extends('layouts.main')

@section('title', 'Paiement Professeurs — Tableau de bord')
@section('breadcrumb-item', 'Paiement Professeurs')
@section('breadcrumb-item-active', 'Tableau de bord')

@section('css')
    <link rel="stylesheet" href="{{ URL::asset('build/css/plugins/style.css') }}">
@endsection

@section('content')

    {{-- Toast --}}
    @if (session('success') || session('error'))
        <div class="position-fixed top-0 end-0 p-3" style="z-index: 99999">
            <div id="liveToast" class="toast hide" role="alert">
                <div class="toast-header">
                    <strong class="me-auto">Paiement Professeurs</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">{{ session('success') ?? session('error') }}</div>
            </div>
        </div>
    @endif

    {{-- Summary Cards --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Groupes avec imports</h6>
                    <h3 class="mb-0">{{ $groups->count() }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Total paiements</h6>
                    <h3 class="mb-0">
                        {{ number_format($groups->sum(fn($g) => (float) ($g->latestPresenceImport?->paymentSummary?->total_payment ?? 0)), 2) }}
                        DH
                    </h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Dernier import</h6>
                    <h3 class="mb-0">
                        @php
                            $lastImport = $groups
                                ->pluck('latestPresenceImport')
                                ->filter()
                                ->sortByDesc('created_at')
                                ->first();
                        @endphp
                        {{ $lastImport ? $lastImport->created_at->format('d/m/Y') : '—' }}
                    </h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-end">
                    <a href="{{ route('backoffice.payroll.presence.import.create') }}" class="btn btn-outline-primary me-1">
                        <i class="ph-duotone ph-upload-simple me-1"></i> Nouvel import
                    </a>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#homeschoolModal">
                        <i class="ph-duotone ph-sync me-1"></i> Generate From Homeschool API
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Homeschool API Modal -->
    <div class="modal fade" id="homeschoolModal" tabindex="-1" aria-labelledby="homeschoolModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="homeschoolForm" action="{{ route('backoffice.payroll.crm.sync') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="homeschoolModalLabel">Generate Payment From Homeschool API</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="site_id" class="form-label">Center</label>
                                <select class="form-select" id="site_id" name="site_id" required
                                    onchange="fetchGroupsForCenter()">
                                    <option value="">Select Center</option>
                                    @foreach ($sites as $site)
                                        <option value="{{ $site->crm_store_id }}">{{ $site->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="group_id" class="form-label">Group/Class</label>
                                <select class="form-select" id="group_id" name="group_id" required disabled>
                                    <option value="">Select Center First</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="date_start" class="form-label">Date Start</label>
                                <input type="date" class="form-control" id="date_start" name="date_start"
                                    value="{{ now()->startOfMonth()->toDateString() }}" required>
                            </div>
                            <div class="col-md-6">
                                <label for="date_end" class="form-label">Date End</label>
                                <input type="date" class="form-control" id="date_end" name="date_end"
                                    value="{{ now()->endOfMonth()->toDateString() }}" required>
                            </div>
                            <div class="col-md-6">
                                <label for="payment_per_student" class="form-label">Taux par étudiant (DH)</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" class="form-control" id="payment_per_student"
                                        name="payment_per_student" placeholder="500" required>
                                    <span class="input-group-text">DH</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="notes" class="form-label">Notes</label>
                                <input type="text" class="form-control" id="notes" name="notes"
                                    placeholder="Optional notes">
                            </div>
                        </div>

                        <!-- Preview Section -->
                        <div id="previewSection" class="mt-4 d-none">
                            <h6>Preview</h6>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="card card-body text-center">
                                        <div class="h3 mb-0" id="previewStudents">0</div>
                                        <div class="text-muted small">Students Found</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card card-body text-center">
                                        <div class="h3 mb-0" id="previewRecords">0</div>
                                        <div class="text-muted small">Attendance Records</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card card-body text-center">
                                        <div class="h3 mb-0" id="previewWeeks">0</div>
                                        <div class="text-muted small">Est. Qualified Weeks</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-outline-primary" id="previewBtn"
                            onclick="previewHomeschoolData()">
                            <i class="ph-duotone ph-eye me-1"></i> Preview
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="ph-duotone ph-sync me-1"></i> Generate Payment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Groups Table --}}
    <div class="row">
        <div class="col-12">
            <div class="card table-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Groupes — Paiement Professeurs</h5>
                    <a href="{{ route('backoffice.payroll.dashboard') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="ph-duotone ph-arrow-left me-1"></i> Suivi Paiement
                    </a>
                </div>
                <div class="card-body pt-3">
                    <div class="table-responsive">
                        <table class="table table-hover" id="pc-dt-simple">
                            <thead>
                                <tr>
                                    <th>Groupe</th>
                                    <th>Professeur</th>
                                    <th>Niveau</th>
                                    <th>Taux/Etudiant</th>
                                    <th>Dernier paiement</th>
                                    <th>Statut</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($groups as $group)
                                    @php
                                        $latest = $group->latestPresenceImport;
                                        $summary = $latest?->paymentSummary;
                                        $rate = $latest?->getEffectivePaymentPerStudent();
                                    @endphp
                                    <tr>
                                        <td>
                                            <strong>{{ $group->name }}</strong>
                                            <br><small class="text-muted">{{ $group->time_range }}</small>
                                        </td>
                                        <td>{{ $group->teacher?->name ?? '—' }}</td>
                                        <td><span class="badge bg-light-primary">{{ $group->level }}</span></td>
                                        <td>{{ $rate ? number_format($rate, 2) . ' DH' : '—' }}</td>
                                        <td>
                                            @if ($summary)
                                                <strong>{{ number_format($summary->total_payment, 2) }} DH</strong>
                                                <br><small class="text-muted">{{ $summary->total_students }} étudiants
                                                    actifs</small>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td>
                                            @if ($summary?->isApproved())
                                                <span class="badge bg-success">Approuvé</span>
                                            @elseif($summary)
                                                <span class="badge bg-warning">En attente</span>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <div class="d-inline-flex gap-2 align-items-center justify-content-end">
                                                <a href="{{ route('backoffice.payroll.presence.group.imports', $group) }}"
                                                    class="btn btn-sm btn-outline-primary" title="Historique">
                                                    <i class="ph-duotone ph-clock-counter-clockwise"></i>
                                                </a>
                                                @if ($latest)
                                                    <a href="{{ route('backoffice.payroll.presence.import.show', ['group' => $group->id, 'import' => $latest->id]) }}"
                                                        class="btn btn-sm btn-outline-success" title="Dernier import">
                                                        <i class="ph-duotone ph-eye"></i>
                                                    </a>
                                                    <form
                                                        action="{{ route('backoffice.payroll.presence.import.destroy', $latest) }}"
                                                        method="POST" class="d-inline"
                                                        onsubmit="return confirm('Supprimer le dernier import de présence de ce groupe ?')">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger"
                                                            title="Supprimer le dernier import">
                                                            <i class="ph-duotone ph-trash"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">
                                            Aucun import de présence pour le moment.
                                            <a href="{{ route('backoffice.payroll.presence.import.create') }}">Importer un
                                                fichier</a>
                                        </td>
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

@section('scripts')
    <script type="module">
        import {
            DataTable
        } from "/build/js/plugins/module.js";
        window.dt = new DataTable("#pc-dt-simple");
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const toastEl = document.getElementById('liveToast');
            if (toastEl) new bootstrap.Toast(toastEl).show();
        });

        async function fetchGroupsForCenter() {
            const strStoreId = document.getElementById('site_id').value;
            const groupSelect = document.getElementById('group_id');
            groupSelect.innerHTML = '<option value="">Loading groups...</option>';
            groupSelect.disabled = true;

            if (!strStoreId) {
                groupSelect.innerHTML = '<option value="">Select Center First</option>';
                return;
            }

            try {
                const response = await fetch('{{ route('backoffice.payroll.crm.classes-for-center') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: new URLSearchParams({
                        'strStoreId': strStoreId
                    })
                });
                const data = await response.json();

                if (data.success && data.groups.length > 0) {
                    groupSelect.disabled = false;
                    groupSelect.innerHTML = '<option value="">Select Group</option>';
                    data.groups.forEach(group => {
                        const option = document.createElement('option');
                        if (group.group_id) {
                            option.value = group.group_id;
                            option.textContent = `${group.name} (${group.level ?? '—'}) - ${group.teacher_name}`;
                        } else {
                            option.value = '';
                            option.disabled = true;
                            option.textContent = `${group.name} — ⚠ Non lié (configurer dans Groupes)`;
                        }
                        groupSelect.appendChild(option);
                    });
                } else {
                    groupSelect.innerHTML = '<option value="">No groups found for this center</option>';
                }
            } catch (error) {
                console.error(error);
                groupSelect.innerHTML = '<option value="">Error loading groups</option>';
            }
        }

        async function previewHomeschoolData() {
            const form = document.getElementById('homeschoolForm');
            const formData = new FormData(form);
            const previewSection = document.getElementById('previewSection');
            const previewBtn = document.getElementById('previewBtn');

            previewBtn.disabled = true;
            previewBtn.innerHTML = '<i class="ph-duotone ph-spinner spinner-border me-1"></i> Loading...';
            previewSection.classList.add('d-none');

            try {
                const response = await fetch('{{ route('backoffice.payroll.crm.preview') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: formData
                });
                const data = await response.json();
                if (data.success) {
                    document.getElementById('previewStudents').textContent = data.students;
                    document.getElementById('previewRecords').textContent = data.records;
                    document.getElementById('previewWeeks').textContent = data.weeks;
                    previewSection.classList.remove('d-none');
                } else {
                    alert('Preview failed: ' + (data.message || 'Unknown error'));
                }
            } catch (error) {
                alert('Error fetching preview: ' + error.message);
            } finally {
                previewBtn.disabled = false;
                previewBtn.innerHTML = '<i class="ph-duotone ph-eye me-1"></i> Preview';
            }
        }
    </script>
@endsection
