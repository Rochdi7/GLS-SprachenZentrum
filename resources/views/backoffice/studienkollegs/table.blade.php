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
                            class="avtar avtar-xs btn-link-secondary me-2" title="Modifier">
                            <i class="ti ti-edit f-20"></i>
                        </a>

                        <form action="{{ route('backoffice.studienkollegs.destroy', $item) }}" method="POST"
                            class="d-inline-block">
                            @csrf
                            @method('DELETE')

                            <button type="submit" class="avtar avtar-xs btn-link-secondary border-0 bg-transparent p-0"
                                onclick="return confirm('Supprimer ce Studienkolleg ?')" title="Supprimer">
                                <i class="ti ti-trash f-20"></i>
                            </button>
                        </form>
                    </td>

                </tr>
            @endforeach
        </tbody>
    </table>
</div>
