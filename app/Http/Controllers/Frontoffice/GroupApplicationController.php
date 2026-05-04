<?php

namespace App\Http\Controllers\Frontoffice;

use App\Http\Controllers\Controller;
use App\Models\Group;
use Illuminate\Http\Request;
use App\Models\GroupApplication;

class GroupApplicationController extends Controller
{
    /**
     * NEW route: POST /groups/apply (group_id comes from hidden input)
     */
    public function storeFromQuery(Request $request)
    {
        $validated = $request->validate([
            'group_id' => ['required', 'integer', 'exists:groups,id'],
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'birthday' => ['nullable', 'date'],
            'note' => ['nullable', 'string'],
        ]);

        $whatsapp = $validated['phone'];

        $existing = \App\Models\GroupApplication::where('group_id', $validated['group_id'])->where('whatsapp_number', $whatsapp)->first();

        // ✅ DUPLICATE
        if ($existing) {
            // no refresh: return JSON if ajax
            if ($request->expectsJson()) {
                return response()->json(
                    [
                        'status' => 'duplicate',
                        'message' => 'Vous avez déjà envoyé une demande pour ce groupe avec ce numéro WhatsApp.',
                    ],
                    409,
                );
            }

            return back()->with('apply_duplicate', 'Vous avez déjà envoyé une demande pour ce groupe avec ce numéro WhatsApp.');
        }

        \App\Models\GroupApplication::create([
            'group_id' => $validated['group_id'],
            'full_name' => $validated['full_name'],
            'email' => $request->email,
            'whatsapp_number' => $whatsapp,
            'birthday' => $validated['birthday'] ?? null,
            'note' => $validated['note'] ?? null,
        ]);

        // ✅ SUCCESS
        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Votre demande a bien été envoyée. Notre équipe vous contactera sous peu.',
            ]);
        }

        return back()->with('success', __('Votre demande a été envoyée avec succès.'));
    }

    /**
     * Legacy route: POST /groups/{group}/apply (group is in URL)
     */
    public function store(Request $request, Group $group)
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'note' => ['nullable', 'string'],
        ]);

        \App\Models\GroupApplication::create([
            'group_id' => $group->id,
            'full_name' => $validated['full_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'address' => $validated['address'] ?? null,
            'start_date' => $validated['start_date'],
            'note' => $validated['note'] ?? null,
            'locale' => app()->getLocale(),
            'source_url' => url()->previous(),
        ]);

        return back()->with('success', __('Votre demande a été envoyée avec succès.'));
    }

    public function approve(Group $group, GroupApplication $application)
    {
        abort_unless((int) $application->group_id === (int) $group->id, 404);

        $application->setStatus('approved')->save();

        return back()->with([
            'status_action' => 'Inscription approuvée avec succès.',
            'status_type' => 'success',
        ]);
    }

    public function reject(Group $group, GroupApplication $application)
    {
        abort_unless((int) $application->group_id === (int) $group->id, 404);

        $application->setStatus('rejected')->save();

        return back()->with([
            'status_action' => 'Inscription refusée.',
            'status_type' => 'danger',
        ]);
    }
}
