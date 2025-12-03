<div class="table-responsive">
    <table class="table table-hover align-middle" id="pc-dt-simple">
        <thead>
            <tr>
                <th>#ID</th>
                <th>Nom du groupe</th>
                <th>Niveau</th>
                <th>Centre</th>
                <th>Enseignant</th>
                <th>Période</th>
                <th>Horaire</th>
                <th>Création</th>
                <th>Actions</th>
            </tr>
        </thead>

        <tbody>
            @forelse($groups as $group)
                <tr>
                    <td>{{ $group->id }}</td>

                    {{-- NOM DU GROUPE --}}
                    <td class="fw-semibold">{{ $group->name }}</td>

                    {{-- LEVEL --}}
                    <td>
                        <span class="badge bg-light-info text-info">
                            {{ $group->level }}
                        </span>
                    </td>

                    {{-- CENTRE --}}
                    <td>
                        <span class="badge bg-light-primary text-primary">
                            {{ $group->site->name ?? '—' }}
                        </span>
                    </td>

                    {{-- ENSEIGNANT --}}
                    <td>{{ $group->teacher->name ?? '—' }}</td>

                    {{-- PERIOD LABEL --}}
                    <td>
                        <span class="badge bg-light-warning text-warning">
                            {{ $group->period_label }}
                        </span>
                    </td>

                    {{-- HORAIRE --}}
                    <td>{{ $group->time_range }}</td>

                    {{-- CREATED --}}
                    <td>{{ $group->created_at->format('Y-m-d') }}</td>

                    {{-- ACTIONS --}}
                    <td>
                        {{-- EDIT --}}
                        <a href="{{ route('backoffice.groups.edit', $group->id) }}"
                           class="avtar avtar-xs btn-link-secondary me-2"
                           title="Modifier">
                            <i class="ti ti-edit f-20"></i>
                        </a>

                        {{-- DELETE --}}
                        <form action="{{ route('backoffice.groups.destroy', $group->id) }}"
                              method="POST" class="d-inline-block">
                            @csrf @method('DELETE')
                            <button class="avtar avtar-xs btn-link-secondary border-0 bg-transparent p-0"
                                    onclick="return confirm('Supprimer ce groupe ?')"
                                    title="Supprimer">
                                <i class="ti ti-trash f-20"></i>
                            </button>
                        </form>
                    </td>
                </tr>

            @empty
                <tr>
                    <td colspan="9" class="text-center text-muted">
                        Aucun groupe trouvé.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
