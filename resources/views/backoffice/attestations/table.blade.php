<div class="table-responsive">
    <table class="table table-hover align-middle" id="pc-dt-simple">
        <thead>
            <tr>
                <th>#ID</th>
                <th>Étudiant</th>
                <th>Groupe</th>
                <th>Centre</th>
                <th>Niveau</th>
                <th>Période niveau</th>
                <th>Unités min</th>
                <th>N° attestation</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($attestations as $att)
                <tr>
                    <td>{{ $att->id }}</td>
                    <td>{{ $att->last_name }} {{ $att->first_name }}</td>
                    <td>{{ $att->group?->name ?? ($att->is_legacy ? '— (ancien)' : '—') }}</td>
                    <td>{{ $att->site?->name ?? $att->group?->site?->name ?? '—' }}</td>
                    <td>
                        <span class="badge bg-light-primary text-primary">{{ $att->level_from && $att->level_from !== $att->level ? $att->level_from . ' → ' . $att->level : $att->level }}</span>
                    </td>
                    <td>
                        {{ $att->niveau_start_date?->format('d/m/Y') }}
                        →
                        {{ $att->niveau_end_date?->format('d/m/Y') }}
                    </td>
                    <td>
                        <span class="badge bg-light-info text-info">{{ $att->units_45min }}</span>
                    </td>
                    <td>{{ $att->attestation_number }}</td>
                    <td>
                        @can('attestations.view')
                            <a href="{{ route('backoffice.attestations.pdf', $att->id) }}"
                               class="avtar avtar-xs btn-link-danger me-2" title="Exporter PDF">
                                <i class="ti ti-download f-20"></i>
                            </a>
                        @endcan

                        @can('attestations.edit')
                            <a href="{{ route('backoffice.attestations.edit', $att->id) }}"
                               class="avtar avtar-xs btn-link-secondary me-2" title="Modifier">
                                <i class="ti ti-edit f-20"></i>
                            </a>
                        @endcan

                        @can('attestations.delete')
                            <form action="{{ route('backoffice.attestations.destroy', $att->id) }}" method="POST" class="d-inline-block">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="avtar avtar-xs btn-link-secondary border-0 bg-transparent p-0"
                                        onclick="return confirm('Supprimer cette attestation ?')"
                                        title="Supprimer">
                                    <i class="ti ti-trash f-20"></i>
                                </button>
                            </form>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center text-muted">Aucune attestation trouvée pour ce centre.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
