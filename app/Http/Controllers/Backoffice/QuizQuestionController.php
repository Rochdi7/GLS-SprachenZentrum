<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backoffice\Quizzes\StoreQuizQuestionRequest;
use App\Http\Requests\Backoffice\Quizzes\UpdateQuizQuestionRequest;
use App\Models\Quiz;
use App\Models\QuizOption;
use App\Models\QuizQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuizQuestionController extends Controller
{
    public function index(Quiz $quiz)
    {
        $questions = $quiz->questions()
            ->with(['options.media', 'media'])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return view('backoffice.quizzes.questions.index', compact('quiz', 'questions'));
    }

    public function create(Quiz $quiz)
    {
        return view('backoffice.quizzes.questions.create', compact('quiz'));
    }

    public function store(StoreQuizQuestionRequest $request, Quiz $quiz)
    {
        $data = $request->validated();

        DB::transaction(function () use ($request, $quiz, $data, &$question) {

            /** @var QuizQuestion $question */
            $question = $quiz->questions()->create([
                'question_text' => $data['question_text'],
                'difficulty'    => $data['difficulty'],
                'points'        => $data['points'],
                'sort_order'    => $data['sort_order'] ?? 0,
                'is_active'     => (bool) ($data['is_active'] ?? false),

                // types
                'question_media_type' => $data['question_media_type'], // none|audio|image
                'options_type'        => $data['options_type'],        // text|image

                // optional meta
                'media_caption' => $data['media_caption'] ?? null,
                'audio_url' => $data['audio_url'] ?? null, // ✅ NEW: Store audio URL
                'media_type'    => 'none', // recomputed after upload
            ]);

            $correctIndex = (int) $data['correct_index'];
            $optionsType  = (string) $data['options_type'];

            foreach ($data['options'] as $i => $opt) {

                $optionText = ($optionsType === 'image')
                    ? null
                    : ($opt['text'] ?? null);

                /** @var QuizOption $option */
                $option = $question->options()->create([
                    'option_text' => $optionText,
                    'is_correct'  => (int) $i === $correctIndex,
                    'sort_order'  => (int) $i,
                ]);

                // Save option image ONLY if options_type=image
                if ($optionsType === 'image' && $request->hasFile("options.$i.image")) {
                    $option->addMediaFromRequest("options.$i.image")
                        ->toMediaCollection('option_image');
                }
            }

            // NEW RULE: Only handle question media upload if options_type != 'image'
            // When options_type='image', question_media_type is FORCED to 'none' by validation
            if ($optionsType !== 'image') {
                $this->handleQuestionMediaUpload($question, $request);
            }

            // OPTIONAL strict cleanup depending on question_media_type selection
            // $this->applyStrictQuestionMediaType($question);
        });

        if ($request->input('action') === 'save_next') {
            return redirect()
                ->route('backoffice.quizzes.questions.create', $quiz)
                ->with('success', 'Question enregistrée. Tu peux ajouter la suivante.');
        }

        return redirect()
            ->route('backoffice.quizzes.questions.index', $quiz)
            ->with('success', 'Question créée avec succès.');
    }

    public function edit(Quiz $quiz, QuizQuestion $question)
    {
        abort_unless($question->quiz_id === $quiz->id, 404);

        $question->load(['options.media', 'media']);

        return view('backoffice.quizzes.questions.edit', compact('quiz', 'question'));
    }

    public function update(UpdateQuizQuestionRequest $request, Quiz $quiz, QuizQuestion $question)
    {
        abort_unless($question->quiz_id === $quiz->id, 404);

        $data = $request->validated();

        DB::transaction(function () use ($request, $question, $data) {

            $question->update([
                'question_text' => $data['question_text'],
                'difficulty'    => $data['difficulty'],
                'points'        => $data['points'],
                'sort_order'    => $data['sort_order'] ?? 0,
                'is_active'     => (bool) ($data['is_active'] ?? false),

                // types
                'question_media_type' => $data['question_media_type'],
                'options_type'        => $data['options_type'],

                // optional meta
                'media_caption' => $data['media_caption'] ?? null,
                'audio_url' => $data['audio_url'] ?? null, // ✅ NEW: Store audio URL
            ]);

            // Reset options + clear their media
            $question->load('options.media');
            foreach ($question->options as $oldOption) {
                $oldOption->clearMediaCollection('option_image');
            }
            $question->options()->delete();

            $correctIndex = (int) $data['correct_index'];
            $optionsType  = (string) $data['options_type'];

            foreach ($data['options'] as $i => $opt) {

                $optionText = ($optionsType === 'image')
                    ? null
                    : ($opt['text'] ?? null);

                /** @var QuizOption $option */
                $option = $question->options()->create([
                    'option_text' => $optionText,
                    'is_correct'  => (int) $i === $correctIndex,
                    'sort_order'  => (int) $i,
                ]);

                if ($optionsType === 'image' && $request->hasFile("options.$i.image")) {
                    $option->addMediaFromRequest("options.$i.image")
                        ->toMediaCollection('option_image');
                }
            }

            // NEW RULE: Only handle question media upload if options_type != 'image'
            // When options_type='image', question_media_type is FORCED to 'none' by validation
            if ($optionsType !== 'image') {
                $this->handleQuestionMediaUpload($question, $request);
            }

            // OPTIONAL strict cleanup depending on question_media_type selection
            // $this->applyStrictQuestionMediaType($question);
        });

        return redirect()
            ->route('backoffice.quizzes.questions.index', $quiz)
            ->with('success', 'Question mise à jour avec succès.');
    }

    public function destroy(Quiz $quiz, QuizQuestion $question)
    {
        abort_unless($question->quiz_id === $quiz->id, 404);

        DB::transaction(function () use ($question) {

            $question->load('options.media');

            // clear question media
            $question->clearMediaCollection('question_image');
            $question->clearMediaCollection('question_audio');
            $question->update(['audio_url' => null]); // ✅ NEW: Clear audio_url column

            // clear option media
            foreach ($question->options as $opt) {
                $opt->clearMediaCollection('option_image');
            }

            $question->delete();
        });

        return back()->with('success', 'Question supprimée avec succès.');
    }

    /**
     * Upload question media if files/URL provided, then recompute media_type.
     * 
     * ✅ NEW: audio_url is stored in DB column (no file upload)
     * - Image still uploaded via Spatie (question_image collection)
     * - Audio now stored as URL in audio_url column
     */
    private function handleQuestionMediaUpload(QuizQuestion $question, Request $request): void
    {
        if ($request->hasFile('image')) {
            $question->addMediaFromRequest('image')
                ->toMediaCollection('question_image');
        }

        // ✅ NEW: Store audio_url from request
        $audioUrl = $request->input('audio_url');
        if ($audioUrl !== null && $audioUrl !== '') {
            $question->update(['audio_url' => $audioUrl]);
        } else {
            // If empty, clear it
            $question->update(['audio_url' => null]);
        }

        // Recompute media_type from stored media + audio_url
        $hasImage = (bool) $question->getFirstMedia('question_image');
        $hasAudio = !empty($question->audio_url) || (bool) $question->getFirstMedia('question_audio');

        $mediaType = 'none';
        if ($hasImage && $hasAudio) {
            $mediaType = 'both';
        } elseif ($hasImage) {
            $mediaType = 'image';
        } elseif ($hasAudio) {
            $mediaType = 'audio';
        }

        $question->update(['media_type' => $mediaType]);
    }

    /**
     * OPTIONAL strict rule:
     * If question_media_type is "none", remove image/audio.
     * If "audio", remove image.
     * If "image", remove audio.
     */
    private function applyStrictQuestionMediaType(QuizQuestion $question): void
    {
        $type = (string) $question->question_media_type;

        if ($type === 'none') {
            $question->clearMediaCollection('question_image');
            $question->clearMediaCollection('question_audio');
        }

        if ($type === 'audio') {
            $question->clearMediaCollection('question_image');
        }

        if ($type === 'image') {
            $question->clearMediaCollection('question_audio');
        }

        // update meta after cleanup
        $hasImage = (bool) $question->getFirstMedia('question_image');
        $hasAudio = (bool) $question->getFirstMedia('question_audio');

        $mediaType = 'none';
        if ($hasImage && $hasAudio) {
            $mediaType = 'both';
        } elseif ($hasImage) {
            $mediaType = 'image';
        } elseif ($hasAudio) {
            $mediaType = 'audio';
        }

        $question->update(['media_type' => $mediaType]);
    }
}
