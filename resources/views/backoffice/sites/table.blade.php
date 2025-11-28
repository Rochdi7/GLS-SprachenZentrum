<div class="table-responsive">
    <table class="table table-hover align-middle" id="pc-dt-simple">
        <thead>
            <tr>
                <th>#ID</th>
                <th>Image</th> {{-- → On garde si tu veux afficher une image générique --}}
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

                    {{-- IMAGE → on affiche placeholder car Hero supprimé --}}
                    <td>
                        <img src="{{ asset('assets/images/placeholder.webp') }}"
                             class="rounded"
                             style="width: 55px; height: 45px; object-fit: cover;"
                             alt="site">
                    </td>

                    <td class="fw-semibold">{{ $site->name }}</td>

                    {{-- VILLE --}}
                    <td>
                        <span class="badge bg-light-primary text-primary">
                            {{ $site->city }}
                        </span>
                    </td>

                    {{-- STATUS --}}
                    <td>
                        @if ($site->is_active)
                            <span class="badge bg-light-success text-success">Actif</span>
                        @else
                            <span class="badge bg-light-danger text-danger">Inactif</span>
                        @endif
                    </td>

                    {{-- CREATED --}}
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
                    {{-- ✔ colspan = 7 car 7 colonnes affichées --}}
                    <td colspan="7" class="text-center text-muted">Aucun centre trouvé.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
