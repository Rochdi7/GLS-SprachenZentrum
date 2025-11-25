<div class="table-responsive">
    <table class="table table-hover align-middle" id="pc-dt-simple">

        <thead>
            <tr>
                <th>#ID</th>
                <th>Nom & Prénom</th>
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
                @php
                    // Totaux calculés (ton controller calcule déjà)
                    $totalScore = $cert->written_total + $cert->oral_total; 
                    $maxScore   = 225 + 75; // 300
                @endphp

                <tr>
                    {{-- ID --}}
                    <td>{{ $cert->id }}</td>

                    {{-- NAME --}}
                    <td>{{ $cert->last_name }} {{ $cert->first_name }}</td>

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
                            {{ $totalScore }} / {{ $maxScore }}
                        </span>
                    </td>

                    {{-- FINAL RESULT --}}
                    <td>
                        @if(Str::contains(strtolower($cert->final_result), 'réussi'))
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
                           class="avtar avtar-xs btn-link-secondary me-2"
                           title="Afficher">
                            <i class="ti ti-eye f-20"></i>
                        </a>

                        {{-- EDIT --}}
                        <a href="{{ route('backoffice.certificates.edit', $cert->id) }}"
                           class="avtar avtar-xs btn-link-secondary me-2"
                           title="Modifier">
                            <i class="ti ti-edit f-20"></i>
                        </a>

                        {{-- DELETE --}}
                        <form action="{{ route('backoffice.certificates.destroy', $cert->id) }}"
                              method="POST" class="d-inline-block">
                            @csrf 
                            @method('DELETE')

                            <button class="avtar avtar-xs btn-link-secondary border-0 bg-transparent p-0"
                                    onclick="return confirm('Supprimer ce certificat ?')"
                                    title="Supprimer">
                                <i class="ti ti-trash f-20"></i>
                            </button>
                        </form>

                    </td>

                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center text-muted">
                        Aucun certificat trouvé.
                    </td>
                </tr>
            @endforelse
        </tbody>

    </table>
</div>
