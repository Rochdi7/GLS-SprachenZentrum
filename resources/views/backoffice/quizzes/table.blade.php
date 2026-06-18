<div class="card">
    <div class="card-body">
        @if (session('success'))
            <div class="alert alert-success mb-3">{{ session('success') }}</div>
        @endif

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width:90px;">Niveau</th>
                        <th>Titre</th>
                        <th style="width:160px;">Questions</th>
                        <th style="width:80px;" class="text-center">Total</th>
                        <th style="width:120px;">Actif</th>
                        <th style="width:260px;" class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($quizzes as $quiz)
                        <tr>
                            <td><strong>{{ $quiz->level }}</strong></td>
                            <td>{{ $quiz->title }}</td>
                            <td>
                                <a href="{{ route('backoffice.quizzes.questions.index', $quiz) }}"
                                    class="btn btn-sm btn-outline-secondary">
                                    Gérer les Questions
                                </a>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-primary rounded-pill">{{ $quiz->questions_count ?? $quiz->questions()->count() }}</span>
                            </td>
                            <td>
                                @if ($quiz->is_active)
                                    <span class="badge bg-success">Oui</span>
                                @else
                                    <span class="badge bg-secondary">Non</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('backoffice.quizzes.edit', $quiz) }}"
                                    class="avtar avtar-xs btn-link-secondary me-2" title="Modifier"
                                    aria-label="Modifier">
                                    <i class="ti ti-edit f-20"></i>
                                </a>

                                <form action="{{ route('backoffice.quizzes.destroy', $quiz) }}" method="POST"
                                    class="d-inline-block">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="avtar avtar-xs btn-link-secondary border-0 bg-transparent p-0"
                                        onclick="return confirm('Supprimer ce quiz ?')" title="Supprimer"
                                        aria-label="Supprimer">
                                        <i class="ti ti-trash f-20"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                Aucun quiz trouvé.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</div>
