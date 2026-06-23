@php
    $isEdit = isset($question);

    // ===== Question types =====
    $questionMediaType = old('question_media_type', $question->question_media_type ?? 'none');
    $optionsType = old('options_type', $question->options_type ?? 'text');

    // ===== Options =====
    $options = old('options');

    if ($options === null) {
        $options = $isEdit
            ? $question->options
                ->sortBy('sort_order')
                ->values()
                ->map(function ($o) {
                    return [
                        'text' => $o->option_text,
                        'image_url' => $o->getFirstMediaUrl('option_image') ?: null,
                    ];
                })
                ->toArray()
            : [
                ['text' => '', 'image_url' => null],
                ['text' => '', 'image_url' => null],
                ['text' => '', 'image_url' => null],
                ['text' => '', 'image_url' => null],
            ];
    }

    // ===== Correct index =====
    $correctIndex = old('correct_index');
    if ($correctIndex === null && $isEdit) {
        $sorted = $question->options->sortBy('sort_order')->values();
        $correctIndex = $sorted->search(fn($o) => (bool) $o->is_correct);
        if ($correctIndex === false) {
            $correctIndex = 0;
        }
    }
    if ($correctIndex === null) {
        $correctIndex = 0;
    }

    // ===== Existing question media =====
    $imageUrl = $isEdit ? $question->getFirstMediaUrl('question_image') : null;
    $audioUrl = $isEdit ? $question->getFirstMediaUrl('question_audio') : null;
@endphp

{{-- ============================= --}}
{{-- QUESTION TYPE SELECTORS --}}
{{-- ============================= --}}
<div class="row g-3 mb-3">

    <div class="col-md-6">
        <label class="form-label">Type de question</label>
        <select name="question_media_type" id="question_media_type"
            class="form-select @error('question_media_type') is-invalid @enderror">
            <option value="none" @selected($questionMediaType === 'none')>Texte seulement</option>
            <option value="audio" @selected($questionMediaType === 'audio')>Audio (Hören)</option>
            <option value="image" @selected($questionMediaType === 'image')>Image</option>
        </select>
        @error('question_media_type')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Type des options</label>
        <select name="options_type" id="options_type" class="form-select @error('options_type') is-invalid @enderror">
            <option value="text" @selected($optionsType === 'text')>Options en texte</option>
            <option value="image" @selected($optionsType === 'image')>Options en images</option>
        </select>
        @error('options_type')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

</div>

{{-- ============================= --}}
{{-- QUESTION TEXT --}}
{{-- ============================= --}}
<div class="mb-3">
    <label class="form-label">Question</label>
    <textarea name="question_text" rows="3" class="form-control @error('question_text') is-invalid @enderror">{{ old('question_text', $question->question_text ?? '') }}</textarea>
    @error('question_text')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

{{-- ============================= --}}
{{-- QUESTION MEDIA --}}
{{-- ============================= --}}
<div class="row g-3 mb-3">

    <div class="col-md-6" id="wrap_image">
        <label class="form-label">Image de la question</label>
        <input type="file" name="image" accept="image/*" class="form-control @error('image') is-invalid @enderror">
        @error('image')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror

        @if ($imageUrl)
            <img src="{{ $imageUrl }}" class="mt-2 rounded" style="max-width:200px">
        @endif
    </div>

    <div class="col-md-6" id="wrap_audio">
        <label class="form-label">URL Audio (Hören)</label>
        <input type="text" name="audio_url" placeholder="https://..."
            value="{{ old('audio_url', $question->audio_url ?? '') }}"
            class="form-control @error('audio_url') is-invalid @enderror">
        @error('audio_url')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <small class="form-text text-muted d-block mt-1">
            Collez un lien direct (Cloudinary/S3) pour écouter l'audio.
        </small>

        @if ($question?->audio_url)
            <audio controls class="mt-2 w-100">
                <source src="{{ $question->audio_url }}">
            </audio>
        @endif
    </div>

</div>

<input type="hidden" name="media_caption" value="{{ old('media_caption', $question->media_caption ?? '') }}">

{{-- ============================= --}}
{{-- OPTIONS --}}
{{-- ============================= --}}
<hr>
<h6>Options</h6>

<div class="alert alert-info mb-3" id="options_hint">
    <small><strong>Mode texte :</strong> Chaque option doit contenir un texte.</small>
</div>

<div class="row g-3">
    @foreach ($options as $i => $opt)
        <div class="col-md-6">
            <div class="border rounded p-2 option-card">

                <div class="d-flex gap-2 align-items-center mb-2">
                    <input type="radio" name="correct_index" value="{{ $i }}"
                        @checked((int) $correctIndex === (int) $i)>
                    <strong>Bonne réponse</strong>
                </div>

                {{-- TEXT OPTION --}}
                <div class="opt-text">
                    <input type="text" name="options[{{ $i }}][text]" value="{{ $opt['text'] ?? '' }}"
                        class="form-control mb-2 @error('options.' . $i . '.text') is-invalid @enderror"
                        placeholder="Texte option {{ $i + 1 }}">
                    @error('options.' . $i . '.text')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                {{-- IMAGE OPTION --}}
                <div class="opt-image">
                    <input type="hidden" name="options[{{ $i }}][has_image]"
                        value="{{ !empty($opt['image_url']) ? 1 : 0 }}">

                    <input type="file" name="options[{{ $i }}][image]" accept="image/*"
                        class="form-control @error('options.' . $i . '.image') is-invalid @enderror">

                    @error('options.' . $i . '.image')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror

                    @if (!empty($opt['image_url']))
                        <img src="{{ $opt['image_url'] }}" class="mt-2 rounded" style="max-width:180px">
                    @endif
                </div>

            </div>
        </div>
    @endforeach
</div>

{{-- ============================= --}}
{{-- META --}}
{{-- ============================= --}}
<hr>
<div class="row g-3">

    <div class="col-md-6">
        <label>Difficulté</label>
        <input type="number" name="difficulty" min="1" max="5"
            value="{{ old('difficulty', $question->difficulty ?? 1) }}" class="form-control">
    </div>

    <div class="col-md-6 d-flex align-items-end">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="is_active" value="1"
                @checked(old('is_active', $question->is_active ?? true))>
            <label class="form-check-label">Actif</label>
        </div>
    </div>

</div>

{{-- ============================= --}}
{{-- JS LOGIC --}}
{{-- ============================= --}}
{{-- NOTE: inline (not @push/@section) because layouts.main uses @yield('scripts'),
     and this partial is @include'd inside the form — a pushed stack would be silently
     dropped, killing the text/image option toggle + submit validation. --}}
    <script>
        (function() {
            const qType = document.getElementById('question_media_type');
            const oType = document.getElementById('options_type');

            const wrapImage = document.getElementById('wrap_image');
            const wrapAudio = document.getElementById('wrap_audio');

            const qImageInput = wrapImage?.querySelector('input[name="image"]');
            const qAudioInput = wrapAudio?.querySelector('input[name="audio_url"]');

            const optionsHint = document.getElementById('options_hint');

            /**
             * Apply question media visibility and required constraints.
             * 
             * IMPORTANT: When options_type='image', the question media (image/audio) is NOT required
             * because the images are in the options themselves. User can choose question_media_type='none'.
             * 
             * Rules:
             * - If options_type='image': HIDE question media inputs (user is using option images)
             * - If options_type='text' AND question_media_type='image': SHOW + REQUIRE question image
             * - If options_type='text' AND question_media_type='audio': SHOW + REQUIRE question audio
             * - If options_type='text' AND question_media_type='none': HIDE both
             */
            function applyQuestionType() {
                const isImageMode = oType.value === 'image';
                const qIsImage = qType.value === 'image';
                const qIsAudio = qType.value === 'audio';

                // NEW RULE: If options_type='image', ALWAYS hide question media and force to 'none'
                if (isImageMode) {
                    // Force and disable question_media_type
                    qType.value = 'none';
                    qType.disabled = true;

                    wrapImage.style.display = 'none';
                    wrapAudio.style.display = 'none';
                    if (qImageInput) qImageInput.required = false;
                    if (qAudioInput) qAudioInput.required = false;
                    if (qImageInput) qImageInput.value = '';
                    if (qAudioInput) qAudioInput.value = '';
                } else {
                    // If options_type='text', enable question_media_type select
                    qType.disabled = false;

                    const showQuestionImage = qIsImage;
                    const showQuestionAudio = qIsAudio;

                    wrapImage.style.display = showQuestionImage ? '' : 'none';
                    wrapAudio.style.display = showQuestionAudio ? '' : 'none';

                    if (qImageInput) qImageInput.required = showQuestionImage;
                    if (qAudioInput) qAudioInput.required = showQuestionAudio;

                    if (!showQuestionImage && qImageInput) qImageInput.value = '';
                    if (!showQuestionAudio && qAudioInput) qAudioInput.value = '';
                }
            }

            function applyOptionsType() {
                const isTextMode = oType.value === 'text';
                const isImageMode = oType.value === 'image';

                // TEXT OPTIONS: show text inputs, hide image inputs
                document.querySelectorAll('.opt-text').forEach(el => {
                    el.style.display = isTextMode ? '' : 'none';
                    el.querySelectorAll('input[type="text"]').forEach(inp => {
                        inp.required = isTextMode;
                        if (isImageMode) inp.value = '';
                    });
                });

                // IMAGE OPTIONS: show image inputs, hide text inputs
                document.querySelectorAll('.opt-image').forEach(el => {
                    el.style.display = isImageMode ? '' : 'none';
                    el.querySelectorAll('input[type="file"]').forEach(inp => {
                        // NEW RULE: In image mode, file required UNLESS existing image (has_image=1)
                        if (isImageMode) {
                            const card = inp.closest('.option-card');
                            const hasExisting = card?.querySelector(
                                'input[type="hidden"][name*="[has_image]"]')?.value === '1';
                            inp.required = !hasExisting;
                        } else {
                            inp.required = false;
                        }
                        if (isTextMode) inp.value = '';
                    });
                });

                // Update hint text
                if (optionsHint) {
                    optionsHint.innerHTML = isTextMode ?
                        '<small><strong>Mode texte :</strong> Chaque option doit contenir un texte.</small>' :
                        '<small><strong>Mode image :</strong> Chaque option doit contenir une image.</small>';
                }

                // Re-apply question type constraints
                applyQuestionType();
            }

            function validateBeforeSubmit(e) {
                const isTextMode = oType.value === 'text';
                const isImageMode = oType.value === 'image';
                const qIsImage = qType.value === 'image';
                const qIsAudio = qType.value === 'audio';

                let hasErrors = false;

                // NEW RULE: Never validate question media in image mode (always 'none' and hidden)
                // Validate question media ONLY when options_type='text'
                if (isTextMode) {
                    if (qIsImage) {
                        const qImageFile = document.querySelector('input[name="image"]')?.files?.length;
                        const existingQImage = !!document.querySelector('#wrap_image img');
                        if (!qImageFile && !existingQImage) {
                            hasErrors = true;
                            wrapImage.classList.add('border', 'border-danger', 'border-2', 'p-2', 'rounded');
                        }
                    }
                    if (qIsAudio) {
                        const qAudioUrl = document.querySelector('input[name="audio_url"]')?.value?.trim();
                        const existingQAudio = !!document.querySelector('#wrap_audio audio');
                        if (!qAudioUrl && !existingQAudio) {
                            hasErrors = true;
                            wrapAudio.classList.add('border', 'border-danger', 'border-2', 'p-2', 'rounded');
                        }
                    }
                }

                // Validate options
                document.querySelectorAll('.option-card').forEach((card, idx) => {
                    card.classList.remove('border-danger', 'border-2');

                    const textInput = card.querySelector('.opt-text input[type="text"]');
                    const fileInput = card.querySelector('.opt-image input[type="file"]');
                    const hasExisting = card.querySelector(
                        '.opt-image input[type="hidden"][name*="[has_image]"]')?.value === '1';

                    if (isTextMode) {
                        // Text mode: require option text
                        const ok = textInput && textInput.value.trim().length > 0;
                        if (!ok) {
                            hasErrors = true;
                            card.classList.add('border-danger', 'border-2');
                        }
                    }

                    if (isImageMode) {
                        // Image mode: require option image (or existing image in edit)
                        const uploaded = fileInput && fileInput.files && fileInput.files.length > 0;
                        const ok = uploaded || hasExisting;
                        if (!ok) {
                            hasErrors = true;
                            card.classList.add('border-danger', 'border-2');
                        }
                    }
                });

                if (hasErrors) {
                    e.preventDefault();
                    if (isTextMode && (qIsImage || qIsAudio)) {
                        alert('⚠ Veuillez compléter la question et toutes les options.');
                    } else {
                        alert('⚠ Veuillez compléter toutes les options : ' + (isTextMode ? 'texte' : 'image') +
                            ' obligatoire.');
                    }
                    return false;
                }

                return true;
            }

            qType.addEventListener('change', applyQuestionType);
            oType.addEventListener('change', applyOptionsType);

            const form = document.querySelector('form');
            if (form) {
                form.addEventListener('submit', validateBeforeSubmit);
            }

            applyQuestionType();
            applyOptionsType();
        })();
    </script>
