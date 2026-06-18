<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backoffice\Quizzes\StoreQuizRequest;
use App\Http\Requests\Backoffice\Quizzes\UpdateQuizRequest;
use App\Models\Quiz;

class QuizController extends Controller
{
    public function index()
    {
        $quizzes = Quiz::withCount('questions')->orderBy('level')->get();

        return view('backoffice.quizzes.index', compact('quizzes'));
    }

    public function create()
    {
        $levels = ['A1','A2','B1','B2'];

        return view('backoffice.quizzes.create', compact('levels'));
    }

    public function store(StoreQuizRequest $request)
    {
        Quiz::create($request->validated());

        return redirect()
            ->route('backoffice.quizzes.index')
            ->with('success', 'Quiz créé avec succès.');
    }

    public function edit(Quiz $quiz)
    {
        $levels = ['A1','A2','B1','B2'];

        return view('backoffice.quizzes.edit', compact('quiz', 'levels'));
    }

    public function update(UpdateQuizRequest $request, Quiz $quiz)
    {
        $quiz->update($request->validated());

        return redirect()
            ->route('backoffice.quizzes.index')
            ->with('success', 'Quiz mis à jour avec succès.');
    }

    public function destroy(Quiz $quiz)
    {
        $quiz->delete();

        return back()->with('success', 'Quiz supprimé avec succès.');
    }
}
