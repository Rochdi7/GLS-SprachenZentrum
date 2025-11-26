<div class="table-responsive">
    <table class="table table-hover align-middle" id="pc-dt-simple">
        <thead>
            <tr>
                <th>#ID</th>
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

                    {{-- LEVEL --}}
                    <td>
                        <span class="badge bg-light-info text-info">
                            {{ $group->level }}
                        </span>
                    </td>

                    {{-- SITE --}}
                    <td>
                        <span class="badge bg-light-primary text-primary">
                            {{ $group->site->name ?? '—' }}
                        </span>
                    </td>

                    {{-- TEACHER --}}
                    <td>{{ $group->teacher->name ?? '—' }}</td>

                    {{-- PERIOD --}}
                    <td>
                        <span class="badge bg-light-warning text-warning">
                            {{ $group->period_label }}
                        </span>
                    </td>

                    {{-- TIME RANGE --}}
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
                    <td colspan="8" class="text-center text-muted">Aucun groupe trouvé.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
