<?php

namespace App\Http\Controllers\Frontoffice;

use App\Http\Controllers\Controller;
use App\Mail\AttestationRequestSubmittedMail;
use App\Models\AttestationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;

class AttestationRequestController extends Controller
{
    public function create()
    {
        return view('frontoffice.attestation-request');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'last_name'   => ['required', 'string', 'max:255'],
            'first_name'  => ['required', 'string', 'max:255'],
            'email'       => ['required', 'email', 'max:255'],
            'phone'       => ['nullable', 'string', 'max:50'],
            'birth_date'  => ['nullable', 'date'],
            'birth_place' => ['nullable', 'string', 'max:255'],
            'group_name'  => ['required', 'string', 'max:255'],
            'level'       => ['required', 'in:A1,A2,B1,B2'],
            'notes'       => ['nullable', 'string', 'max:2000'],
        ], [
            'last_name.required'   => 'Le nom est obligatoire.',
            'first_name.required'  => 'Le prénom est obligatoire.',
            'email.required'       => 'L\'email est obligatoire.',
            'email.email'          => 'L\'email n\'est pas valide.',
            'group_name.required'  => 'Veuillez préciser le nom de votre groupe.',
            'level.required'       => 'Veuillez sélectionner un niveau.',
            'notes.max'            => 'Les notes ne peuvent pas dépasser 2000 caractères.',
        ]);

        $attRequest = AttestationRequest::create($validated);

        try {
            Mail::to('info@glssprachenzentrum.ma')->send(new AttestationRequestSubmittedMail($attRequest));
        } catch (\Throwable $e) {
            Log::error('Attestation request email failed: ' . $e->getMessage(), [
                'request_id' => $attRequest->id,
            ]);
        }

        return Redirect::route('front.attestation-request.success');
    }

    public function success()
    {
        return view('frontoffice.attestation-request-success');
    }
}
