<div class="table-responsive">
    <table class="table table-hover align-middle" id="pc-dt-simple">
        <thead>
            <tr>
                <th>#ID</th>
                <th>Nom</th>
                <th>Email</th>
                <th>Rôle</th>
                <th>Centre</th>
                <th>Poste</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
        </thead>

        <tbody>
            @forelse($users as $user)
                <tr>
                    <td>{{ $user->id }}</td>

                    <td>{{ $user->name }}</td>

                    <td>{{ $user->email }}</td>

                    <td>
                        @forelse($user->roles as $role)
                            <span class="badge bg-light-primary">{{ $role->name }}</span>
                        @empty
                            <span class="badge bg-light-warning">Aucun rôle</span>
                        @endforelse
                    </td>

                    <td>
                        @php
                            $primaryId = $user->site_id;
                            $allSites  = $user->sites ?? collect();
                            // Show primary first, then any extra centres
                            $primary   = $user->site;
                            $extras    = $allSites->reject(fn ($s) => (int) $s->id === (int) $primaryId);
                        @endphp

                        @if(!$primary && $extras->isEmpty())
                            <span class="text-muted">—</span>
                        @else
                            <div class="d-flex flex-wrap gap-1">
                                @if($primary)
                                    <span class="badge bg-light-info" title="Centre principal">
                                        {{ $primary->name }}
                                    </span>
                                @endif
                                @foreach($extras as $extra)
                                    <span class="badge bg-light-secondary" title="Centre additionnel">
                                        {{ $extra->name }}
                                    </span>
                                @endforeach
                            </div>
                        @endif
                    </td>

                    <td>
                        @if($user->staff_role)
                            <span class="badge bg-light-secondary">{{ $user->staff_role }}</span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>

                    <td>
                        @if($user->is_active)
                            <span class="badge bg-light-success">Actif</span>
                        @else
                            <span class="badge bg-light-danger">Inactif</span>
                        @endif
                    </td>

                    <td>

                        {{-- EDIT --}}
                        @can('users.edit')
                        <a href="{{ route('backoffice.users.edit', $user->id) }}"
                            class="avtar avtar-xs btn-link-secondary me-2" title="Modifier" aria-label="Modifier">
                            <i class="ti ti-edit f-20"></i>
                        </a>
                        @endcan

                        {{-- DELETE --}}
                        @can('users.delete')
                        @if($user->id !== auth()->id())
                            <form action="{{ route('backoffice.users.destroy', $user->id) }}" method="POST"
                                class="d-inline-block">
                                @csrf @method('DELETE')
                                <button type="submit" class="avtar avtar-xs btn-link-secondary border-0 bg-transparent p-0"
                                    onclick="return confirm('Supprimer cet utilisateur ?')" title="Supprimer"
                                    aria-label="Supprimer">
                                    <i class="ti ti-trash f-20"></i>
                                </button>
                            </form>
                        @endif
                        @endcan

                    </td>

                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center text-muted">Aucun utilisateur trouvé.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
