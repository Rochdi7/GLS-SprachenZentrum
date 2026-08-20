<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\GlsInscription;
use App\Models\GlsInscriptionStep1Data;
use Illuminate\Http\Request;

/**
 * Partial leads captured at step 1 of the GLS inscription modal.
 *
 * A row here means the visitor filled in their contact details and clicked
 * "Continuer" — it does NOT mean they finished the inscription. Rows whose
 * email also exists in gls_inscriptions are flagged as converted so the team
 * can focus on the ones that dropped off.
 */
class GlsInscriptionStep1Controller extends Controller
{
    public function index(Request $request)
    {
        $query = GlsInscriptionStep1Data::query();

        if ($search = trim((string) $request->input('q'))) {
            $query->where(function ($q) use ($search) {
                $q->where('email', 'like', '%'.$search.'%')
                    ->orWhere('nom', 'like', '%'.$search.'%')
                    ->orWhere('prenom', 'like', '%'.$search.'%')
                    ->orWhere('phone', 'like', '%'.$search.'%');
            });
        }

        if ($source = $request->input('form_source')) {
            $query->where('form_source', $source);
        }

        $leads = $query->orderByDesc('created_at')->orderByDesc('id')->get();

        // A partial lead is "converted" once the same email completed a full inscription.
        $completedEmails = GlsInscription::query()
            ->whereIn('email', $leads->pluck('email')->filter())
            ->pluck('email')
            ->map(fn ($email) => mb_strtolower($email))
            ->flip();

        $leads->each(function ($lead) use ($completedEmails) {
            $lead->is_converted = $completedEmails->has(mb_strtolower((string) $lead->email));
        });

        $convertedCount = $leads->where('is_converted', true)->count();

        $stats = [
            'total' => GlsInscriptionStep1Data::count(),
            'today' => GlsInscriptionStep1Data::whereDate('created_at', now()->toDateString())->count(),
            'this_week' => GlsInscriptionStep1Data::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'abandoned' => $leads->count() - $convertedCount,
        ];

        $sources = GlsInscriptionStep1Data::query()
            ->whereNotNull('form_source')
            ->distinct()
            ->pluck('form_source');

        return view('backoffice.gls_step1_leads.index', compact('leads', 'stats', 'sources'));
    }

    public function destroy(GlsInscriptionStep1Data $lead)
    {
        $lead->delete();

        return redirect()
            ->route('backoffice.gls_step1_leads.index')
            ->with('success', 'Lead supprimé avec succès.');
    }
}
