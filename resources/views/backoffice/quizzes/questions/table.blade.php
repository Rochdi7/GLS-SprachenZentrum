<div class="card">
    <div class="card-body">
        @if (session('success'))
            <div class="alert alert-success mb-3">{{ session('success') }}</div>
        @endif

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width:80px;">Difficultés</th>
                        <th>Question</th>
                        <th style="width:90px;">Points</th>
                        <th style="width:120px;">Actif</th>
                        <th style="width:220px;" class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($questions as $q)
                        <tr>
                            <td><span class="badge bg-info">{{ $q->difficulty }}</span></td>
                            <td>
                                <div class="fw-semibold">{{ \Illuminate\Support\Str::limit($q->question_text, 120) }}
                                </div>
                                <div class="text-muted small">
                                    @php $correctOption = $q->options->firstWhere('is_correct', true); @endphp
                                    Correct:
                                    @if($correctOption)
                                        <span class="text-success fw-semibold">{{ $correctOption->option_text }}</span>
                                    @else
                                        <span class="text-danger">non défini</span>
                                    @endif
                                </div>
                            </td>
                            <td>{{ $q->points }}</td>
                            <td>
                                @if ($q->is_active)
                                    <span class="badge bg-success">Oui</span>
                                @else
                                    <span class="badge bg-secondary">Non</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('backoffice.quizzes.questions.edit', [$quiz, $q]) }}"
                                    class="avtar avtar-xs btn-link-secondary me-2" title="Modifier"
                                    aria-label="Modifier">
                                    <i class="ti ti-edit f-20"></i>
                                </a>

                                <form action="{{ route('backoffice.quizzes.questions.destroy', [$quiz, $q]) }}"
                                    method="POST" class="d-inline-block">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="avtar avtar-xs btn-link-secondary border-0 bg-transparent p-0"
                                        onclick="return confirm('Supprimer cette question ?')" title="Supprimer"
                                        aria-label="Supprimer">
                                        <i class="ti ti-trash f-20"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                Aucune question trouvée.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
