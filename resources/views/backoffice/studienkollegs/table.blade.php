<div class="table-responsive">
    <table class="table table-hover" id="pc-dt-simple">
        <thead>
            <tr>
                <th>Nom</th>
                <th>Ville</th>
                <th>Uni-Assist</th>
                <th>Featured</th>
                <th>Statut</th>
                <th class="text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($studienkollegs as $item)
                <tr>
                    <td>{{ $item->name }}</td>
                    <td>{{ $item->city }}</td>
                    <td>
                        <span class="badge {{ $item->uni_assist ? 'bg-success' : 'bg-secondary' }}">
                            {{ $item->uni_assist ? 'Oui' : 'Non' }}
                        </span>
                    </td>
                    <td>
                        <span class="badge {{ $item->featured ? 'bg-warning' : 'bg-secondary' }}">
                            {{ $item->featured ? 'Oui' : 'Non' }}
                        </span>
                    </td>
                    <td>
                        <span class="badge {{ $item->public ? 'bg-success' : 'bg-danger' }}">
                            {{ $item->public ? 'Public' : 'Privé' }}
                        </span>
                    </td>
                    <td class="text-end">
                        <a href="{{ route('backoffice.studienkollegs.edit', $item) }}"
                           class="btn btn-sm btn-outline-primary">
                            Modifier
                        </a>

                        <form action="{{ route('backoffice.studienkollegs.destroy', $item) }}"
                              method="POST"
                              class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"
                                    onclick="return confirm('Supprimer ce Studienkolleg ?')">
                                Supprimer
                            </button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
