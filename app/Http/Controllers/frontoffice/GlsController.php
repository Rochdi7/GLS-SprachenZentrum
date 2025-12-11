<?php

namespace App\Http\Controllers\Frontoffice;

use App\Http\Controllers\Controller;
use App\Models\GlsInscription;
use App\Models\Site;
use Illuminate\Http\Request;
use App\Mail\GlsInscriptionMail;
use App\Mail\GlsInscriptionConfirmation;
use Illuminate\Support\Facades\Mail;

class GlsController extends Controller
{
    public function store(Request $request)
{
    // Validate inputs
    $validated = $request->validate([
        'name'   => 'required|string|max:255',
        'email'  => 'required|email',
        'phone'  => 'required',
        'adresse'=> 'required',
        'niveau' => 'required',
        'centre' => 'required|integer|exists:sites,id',
        'group_id' => 'required|integer|exists:groups,id',
    ]);

    // Duplicate protection
    $exists = GlsInscription::where('email', $request->email)
                            ->where('centre', $request->centre)
                            ->exists();

    if ($exists) {
        return response()->json([
            'status'  => 'duplicate',
            'message' => 'Vous avez déjà fait une demande pour ce centre.',
        ], 409);
    }

    // Save in DB
    $inscription = GlsInscription::create($request->all());

    // Load related objects
    $centre = Site::find($request->centre);
    $group  = \App\Models\Group::find($request->group_id);

    // Admin email
    Mail::to('rochdi.karouali1234@gmail.com')
        ->send(new GlsInscriptionMail($request->all(), $centre, $group));

    // Student confirmation email
    Mail::to($request->email)
        ->send(new GlsInscriptionConfirmation($request->all(), $centre, $group));

    return response()->json([
        'status'  => 'success',
        'message' => 'Inscription enregistrée. Email envoyé.',
    ]);
}

}
