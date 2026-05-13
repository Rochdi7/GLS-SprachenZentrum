<?php

namespace App\Http\Controllers\Frontoffice;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class FeedbackController extends Controller
{
    public function create()
    {
        $sites = Site::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'city']);

        return view('frontoffice.feedback', [
            'sites' => $sites,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'site_id'   => ['required', 'integer', 'exists:sites,id'],
            'message'   => ['required', 'string', 'min:5', 'max:5000'],
        ], [
            'full_name.required' => 'Le nom complet est obligatoire.',
            'site_id.required'   => 'Veuillez sélectionner votre centre GLS.',
            'site_id.exists'     => 'Le centre sélectionné est invalide.',
            'message.required'   => 'Le message est obligatoire.',
            'message.min'        => 'Le message doit contenir au moins 5 caractères.',
            'message.max'        => 'Le message ne peut pas dépasser 5000 caractères.',
        ]);

        $site = Site::find($validated['site_id']);

        Feedback::create([
            'full_name'          => $validated['full_name'],
            'site_id'            => $site?->id,
            'site_name_snapshot' => $site?->name,
            'message'            => $validated['message'],
        ]);

        return Redirect::route('front.feedback.success');
    }

    public function success()
    {
        return view('frontoffice.feedback-success');
    }
}
