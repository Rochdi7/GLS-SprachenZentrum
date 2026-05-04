<div class="table-responsive">
    <table class="table table-hover align-middle" id="pc-dt-simple">

        <thead>
            <tr>
                <th>#ID</th>
                <th>Nom & Prénom</th>
                <th>Centre</th>
                <th>Niveau</th>
                <th>Date Examen</th>
                <th>Score Total</th>
                <th>Résultat Final</th>
                <th>N° Certificat</th>
                <th>Actions</th>
            </tr>
        </thead>

        <tbody>
            @forelse($certificates as $cert)
                <tr>
                    {{-- ID --}}
                    <td>{{ $cert->id }}</td>

                    {{-- NAME --}}
                    <td>{{ $cert->last_name }} {{ $cert->first_name }}</td>

                    {{-- CENTRE --}}
                    <td>{{ $cert->site?->name ?? '—' }}</td>

                    {{-- LEVEL --}}
                    <td>
                        <span class="badge bg-light-primary text-primary">
                            {{ $cert->exam_level }}
                        </span>
                    </td>

                    {{-- EXAM DATE --}}
                    <td>{{ $cert->exam_date->format('Y-m-d') }}</td>

                    {{-- TOTAL SCORE --}}
                    <td>
                        <span class="badge bg-light-info text-info">
                            {{ $cert->total_score }} / {{ $cert->total_max }}
                        </span>
                    </td>

                    {{-- FINAL RESULT --}}
                    <td>
                        @if (Str::contains(strtolower($cert->final_result), 'réussi'))
                            <span class="badge bg-light-success text-success">
                                {{ $cert->final_result }}
                            </span>
                        @else
                            <span class="badge bg-light-danger text-danger">
                                {{ $cert->final_result }}
                            </span>
                        @endif
                    </td>

                    {{-- CERTIFICATE NUMBER --}}
                    <td>{{ $cert->certificate_number }}</td>

                    {{-- ACTIONS --}}
                    <td>

                        {{-- VIEW --}}
                        <a href="{{ route('backoffice.certificates.show', $cert->id) }}"
                            class="avtar avtar-xs btn-link-secondary me-2" title="Voir" aria-label="Voir">
                            <i class="ti ti-eye f-20"></i>
                        </a>

                        {{-- EXPORT PDF --}}
                        <a href="{{ route('backoffice.certificates.pdf', $cert->id) }}"
                            class="avtar avtar-xs btn-link-danger me-2" title="Exporter PDF" aria-label="Exporter PDF">
                            <i class="ti ti-download f-20"></i>
                        </a>

                        {{-- EDIT --}}
                        <a href="{{ route('backoffice.certificates.edit', $cert->id) }}"
                            class="avtar avtar-xs btn-link-secondary me-2" title="Modifier" aria-label="Modifier">
                            <i class="ti ti-edit f-20"></i>
                        </a>

                        {{-- DELETE --}}
                        <form action="{{ route('backoffice.certificates.destroy', $cert->id) }}" method="POST"
                            class="d-inline-block">
                            @csrf
                            @method('DELETE')

                            <button type="submit" class="avtar avtar-xs btn-link-secondary border-0 bg-transparent p-0"
                                onclick="return confirm('Supprimer ce certificat ?')" title="Supprimer"
                                aria-label="Supprimer">
                                <i class="ti ti-trash f-20"></i>
                            </button>
                        </form>

                    </td>

                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center text-muted">
                        Aucun certificat trouvé pour ce centre.
                    </td>
                </tr>
            @endforelse
        </tbody>

    </table>
</div>
