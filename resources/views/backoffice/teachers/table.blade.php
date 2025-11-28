<div class="table-responsive">
    <table class="table table-hover align-middle" id="pc-dt-simple">
        <thead>
            <tr>
                <th>#ID</th>
                <th>Photo</th>
                <th>Nom</th>
                <th>Site</th>
                <th>Spécialité</th>
                <th>Email</th>
                <th>Téléphone</th>
                <th>Création</th>
                <th>Actions</th>
            </tr>
        </thead>

        <tbody>
            @forelse($teachers as $teacher)
                <tr>
                    <td>{{ $teacher->id }}</td>

                    {{-- IMAGE --}}
                    <td>
                        @php
                            $media = $teacher->getFirstMedia('teacher_image');
                        @endphp

                        <img src="{{ $media
                                    ? $media->getUrl()
                                    : asset('assets/images/placeholder.webp') }}"
                             alt="teacher-photo"
                             class="rounded-circle"
                             style="width: 45px; height: 45px; object-fit: cover;">
                    </td>

                    {{-- NAME --}}
                    <td>{{ $teacher->name }}</td>

                    {{-- SITE --}}
                    <td>
                        <span class="badge bg-light-primary text-primary">
                            {{ $teacher->site->name ?? '—' }}
                        </span>
                    </td>

                    {{-- SPECIALITY --}}
                    <td>{{ $teacher->speciality ?? '—' }}</td>

                    {{-- EMAIL --}}
                    <td>{{ $teacher->email ?? '—' }}</td>

                    {{-- PHONE --}}
                    <td>{{ $teacher->phone ?? '—' }}</td>

                    {{-- CREATED --}}
                    <td>{{ $teacher->created_at->format('Y-m-d') }}</td>

                    {{-- ACTIONS --}}
                    <td>

                        {{-- EDIT --}}
                        <a href="{{ route('backoffice.teachers.edit', $teacher->id) }}"
                            class="avtar avtar-xs btn-link-secondary me-2" title="Modifier">
                            <i class="ti ti-edit f-20"></i>
                        </a>

                        {{-- DELETE --}}
                        <form action="{{ route('backoffice.teachers.destroy', $teacher->id) }}"
                              method="POST" class="d-inline-block">
                            @csrf @method('DELETE')
                            <button class="avtar avtar-xs btn-link-secondary border-0 bg-transparent p-0"
                                onclick="return confirm('Supprimer cet enseignant ?')" title="Supprimer">
                                <i class="ti ti-trash f-20"></i>
                            </button>
                        </form>

                    </td>

                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center text-muted">Aucun enseignant trouvé.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
