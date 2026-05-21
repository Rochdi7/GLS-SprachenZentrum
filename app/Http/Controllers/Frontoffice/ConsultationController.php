<?php

namespace App\Http\Controllers\Frontoffice;

use App\Http\Controllers\Controller;
use App\Jobs\SyncLeadToGoogleSheetJob;
use App\Models\Consultation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\ConsultationAdminMail;
use App\Mail\ConsultationConfirmationMail;

class ConsultationController extends Controller
{
    public function store(Request $request)
    {
        // Validate inputs
        $validated = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'city'  => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['required', 'email', 'max:255'],
        ]);

        // Block if 3+ consultations already exist for this email or phone
        $existingCount = Consultation::where('email', $validated['email'])
            ->orWhere('phone', $validated['phone'])
            ->count();

        if ($existingCount >= 3) {
            return response()->json([
                'status'  => 'blocked',
                'message' => 'Vous avez atteint le nombre maximum de demandes (3). Veuillez nous contacter directement.',
            ], 429);
        }

        // Save in DB
        $consultation = Consultation::create($validated);

        // Sync to Google Sheets
        SyncLeadToGoogleSheetJob::dispatch($consultation);

        // Admin email
        Mail::to('info@gls-sprachzentrum.ma')
            ->send(new ConsultationAdminMail($consultation));

        // Client confirmation email
        Mail::to($consultation->email)
            ->send(new ConsultationConfirmationMail($consultation));

        return response()->json([
            'status'  => 'success',
            'message' => 'Votre demande a bien été envoyée. Email envoyé.',
        ]);
    }
}
