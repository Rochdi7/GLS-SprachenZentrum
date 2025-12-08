<div class="table-responsive">
    <table class="table table-hover align-middle" id="pc-dt-simple">
        <thead>
            <tr>
                <th>#ID</th>
                <th>Nom du site</th>
                <th>Ville</th>
                <th>Statut</th>
                <th>Création</th>
                <th>Actions</th>
            </tr>
        </thead>

        <tbody>
            @forelse($sites as $site)
                <tr>
                    <td>{{ $site->id }}</td>

                    {{-- NOM DU SITE --}}
                    <td class="fw-semibold">{{ $site->name }}</td>

                    {{-- VILLE --}}
                    <td>
                        <span class="badge bg-light-primary text-primary">
                            {{ $site->city }}
                        </span>
                    </td>

                    {{-- STATUT --}}
                    <td>
                        @if ($site->is_active)
                            <span class="badge bg-light-success text-success">Actif</span>
                        @else
                            <span class="badge bg-light-danger text-danger">Inactif</span>
                        @endif
                    </td>

                    {{-- DATE DE CREATION --}}
                    <td>{{ $site->created_at->format('Y-m-d') }}</td>

                    {{-- ACTIONS --}}
                    <td>
                        <a href="{{ route('backoffice.sites.edit', $site->id) }}"
                           class="avtar avtar-xs btn-link-secondary me-2" title="Modifier">
                            <i class="ti ti-edit f-20"></i>
                        </a>

                        <form action="{{ route('backoffice.sites.destroy', $site->id) }}"
                              method="POST" class="d-inline-block">
                            @csrf @method('DELETE')
                            <button class="avtar avtar-xs btn-link-secondary border-0 bg-transparent p-0"
                                onclick="return confirm('Supprimer ce centre ?')" title="Supprimer">
                                <i class="ti ti-trash f-20"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    {{-- ✔ colspan = 6 car 6 colonnes maintenant --}}
                    <td colspan="6" class="text-center text-muted">Aucun centre trouvé.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
