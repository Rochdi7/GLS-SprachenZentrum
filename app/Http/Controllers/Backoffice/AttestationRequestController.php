<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Mail\AttestationRequestAcceptedMail;
use App\Mail\AttestationRequestRefusedMail;
use App\Models\AttestationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AttestationRequestController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');

        $query = AttestationRequest::query()
            ->with('reviewer')
            ->latest();

        if (in_array($status, [AttestationRequest::STATUS_PENDING, AttestationRequest::STATUS_ACCEPTED, AttestationRequest::STATUS_REFUSED], true)) {
            $query->where('status', $status);
        }

        $requests = $query->get();

        $counts = [
            'all'      => AttestationRequest::count(),
            'pending'  => AttestationRequest::where('status', AttestationRequest::STATUS_PENDING)->count(),
            'accepted' => AttestationRequest::where('status', AttestationRequest::STATUS_ACCEPTED)->count(),
            'refused'  => AttestationRequest::where('status', AttestationRequest::STATUS_REFUSED)->count(),
        ];

        return view('backoffice.attestation_requests.index', [
            'requests'      => $requests,
            'currentStatus' => $status,
            'counts'        => $counts,
        ]);
    }

    public function show(string $id)
    {
        $attRequest = AttestationRequest::with(['reviewer', 'attestation'])->findOrFail($id);

        return view('backoffice.attestation_requests.show', [
            'request' => $attRequest,
        ]);
    }

    /**
     * Accept the request → mark as accepted, email the requester,
     * then redirect to the attestation create form prefilled with the request's data.
     */
    public function accept(string $id)
    {
        $attRequest = AttestationRequest::findOrFail($id);

        if (!$attRequest->isPending()) {
            return redirect()->route('backoffice.attestation_requests.show', $attRequest->id)
                ->with('error', 'Cette demande a déjà été traitée.');
        }

        $attRequest->update([
            'status'      => AttestationRequest::STATUS_ACCEPTED,
            'reviewed_at' => now(),
            'reviewed_by' => auth()->id(),
        ]);

        try {
            Mail::to($attRequest->email)->send(new AttestationRequestAcceptedMail($attRequest));
        } catch (\Throwable $e) {
            Log::error('Failed to send acceptance email: ' . $e->getMessage(), [
                'request_id' => $attRequest->id,
            ]);
        }

        return redirect()->route('backoffice.attestations.create', ['from_request' => $attRequest->id])
            ->with('success', 'Demande acceptée. Complétez l\'attestation ci-dessous.');
    }

    /**
     * Refuse the request — requires a reason that's emailed to the requester.
     */
    public function refuse(Request $request, string $id)
    {
        $validated = $request->validate([
            'refusal_reason' => ['required', 'string', 'min:5', 'max:5000'],
        ], [
            'refusal_reason.required' => 'Le motif du refus est obligatoire.',
            'refusal_reason.min'      => 'Le motif doit contenir au moins 5 caractères.',
        ]);

        $attRequest = AttestationRequest::findOrFail($id);

        if (!$attRequest->isPending()) {
            return redirect()->route('backoffice.attestation_requests.show', $attRequest->id)
                ->with('error', 'Cette demande a déjà été traitée.');
        }

        $attRequest->update([
            'status'         => AttestationRequest::STATUS_REFUSED,
            'refusal_reason' => $validated['refusal_reason'],
            'reviewed_at'    => now(),
            'reviewed_by'    => auth()->id(),
        ]);

        try {
            Mail::to($attRequest->email)->send(new AttestationRequestRefusedMail($attRequest));
        } catch (\Throwable $e) {
            Log::error('Failed to send refusal email: ' . $e->getMessage(), [
                'request_id' => $attRequest->id,
            ]);
        }

        return redirect()->route('backoffice.attestation_requests.index')
            ->with('success', 'Demande refusée. L\'étudiant a été notifié par email.');
    }

    public function destroy(string $id)
    {
        abort_unless(auth()->user()->hasRole('Super Admin'), 403);

        AttestationRequest::findOrFail($id)->delete();

        return redirect()->route('backoffice.attestation_requests.index')
            ->with('success', 'Demande supprimée.');
    }
}
