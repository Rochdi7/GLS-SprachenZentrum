<?php

namespace App\Http\Controllers\Frontoffice;

use App\Http\Controllers\Controller;
use App\Models\GlsInscription;
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
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'required',
            'adresse' => 'required',
            'niveau' => 'required',
            'centre' => 'required',
        ]);

        // Duplicate protection
        $exists = GlsInscription::where('email', $request->email)->where('centre', $request->centre)->exists();

        if ($exists) {
            return response()->json(
                [
                    'status' => 'duplicate',
                    'message' => 'Vous avez déjà fait une demande pour ce centre.',
                ],
                409,
            );
        }

        // Save in DB
        $inscription = GlsInscription::create($request->all());

        // Admin email
        Mail::to('rochdi.karouali1234@gmail.com')->send(new GlsInscriptionMail($request->all()));

        // Student confirmation email
        Mail::to($request->email)->send(new GlsInscriptionConfirmation($request->all()));

        return response()->json([
            'status' => 'success',
            'message' => 'Inscription enregistrée. Email envoyé à l’admin et au client.',
        ]);
    }
}
