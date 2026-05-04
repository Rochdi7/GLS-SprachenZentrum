<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Concerns\ScopesToUserSites;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backoffice\Attestations\StoreAttestationRequest;
use App\Http\Requests\Backoffice\Attestations\UpdateAttestationRequest;
use App\Models\Attestation;
use App\Models\AttestationRequest;
use App\Models\Group;
use App\Models\GroupLevelFollowup;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttestationController extends Controller
{
    use ScopesToUserSites;

    private const ORDER = ['A1', 'A2', 'B1', 'B2', 'C1'];

    private const ERFOLG_OPTIONS = [
        'Erfolg'             => 'Très bien',
        'mit gutem Erfolg'   => 'Bien',
        'mit Erfolg'         => 'Assez bien',
        'teilgenommen'       => 'Participation régulière',
    ];

    private const LANGUAGE_OPTIONS = [
        'de_fr' => 'Bilingue (Allemand / Français)',
    ];

    public function index(Request $request)
    {
        $query = Attestation::with('group.site', 'site')->latest();

        // Centre access scope (Super Admin / Admin: no scope; others: only their centres)
        $this->scopeToUserSites($query);

        // Optional centre filter dropdown (only meaningful for users with multiple centres)
        $requestedSiteId = $this->resolveRequestedSiteId(
            $request->filled('site_id') ? (int) $request->site_id : null
        );
        if ($requestedSiteId) {
            $query->where('site_id', $requestedSiteId);
        }

        $attestations = $query->get();
        $sites        = $this->accessibleSites();

        return view('backoffice.attestations.index', compact('attestations', 'sites', 'requestedSiteId'));
    }

    public function create(Request $request)
    {
        $prefillRequest = null;
        if ($request->filled('from_request')) {
            $prefillRequest = AttestationRequest::find($request->query('from_request'));
        }

        $sites = $this->accessibleSites();

        return view('backoffice.attestations.create', [
            'groups'           => $this->groupsForSelect(),
            'sites'            => $sites,
            'erfolgOptions'    => self::ERFOLG_OPTIONS,
            'languageOptions'  => self::LANGUAGE_OPTIONS,
            'prefillRequest'   => $prefillRequest,
        ]);
    }

    public function store(StoreAttestationRequest $request)
    {
        $data = $this->hydrate($request->validated());

        // Block users that aren't allowed to manage this centre.
        abort_unless(
            $this->userSeesAllSites() ||
            ($data['site_id'] !== null && in_array((int) $data['site_id'], auth()->user()->accessibleSiteIds(), true)),
            403,
            "Vous n'avez pas accès à ce centre."
        );

        $attestation = Attestation::create($data);

        // If this attestation was created from an accepted demand, link them.
        if ($request->filled('from_request')) {
            $attRequest = AttestationRequest::find($request->input('from_request'));
            if ($attRequest && $attRequest->status === AttestationRequest::STATUS_ACCEPTED) {
                $attRequest->update(['attestation_id' => $attestation->id]);
            }
        }

        return redirect()->route('backoffice.attestations.index')
            ->with('success', 'Attestation ajoutée avec succès.');
    }

    public function edit(string $id)
    {
        $attestation = Attestation::findOrFail($id);

        abort_unless(
            $this->userSeesAllSites() ||
            in_array((int) $attestation->site_id, auth()->user()->accessibleSiteIds(), true),
            403
        );

        return view('backoffice.attestations.edit', [
            'attestation'      => $attestation,
            'groups'           => $this->groupsForSelect(),
            'sites'            => $this->accessibleSites(),
            'erfolgOptions'    => self::ERFOLG_OPTIONS,
            'languageOptions'  => self::LANGUAGE_OPTIONS,
        ]);
    }

    public function update(UpdateAttestationRequest $request, string $id)
    {
        $attestation = Attestation::findOrFail($id);

        abort_unless(
            $this->userSeesAllSites() ||
            in_array((int) $attestation->site_id, auth()->user()->accessibleSiteIds(), true),
            403
        );

        $data = $this->hydrate($request->validated());

        abort_unless(
            $this->userSeesAllSites() ||
            ($data['site_id'] !== null && in_array((int) $data['site_id'], auth()->user()->accessibleSiteIds(), true)),
            403,
            "Vous n'avez pas accès à ce centre."
        );

        $attestation->update($data);

        return redirect()->route('backoffice.attestations.index')
            ->with('success', 'Attestation mise à jour avec succès.');
    }

    public function destroy(string $id)
    {
        $attestation = Attestation::findOrFail($id);

        abort_unless(
            $this->userSeesAllSites() ||
            in_array((int) $attestation->site_id, auth()->user()->accessibleSiteIds(), true),
            403
        );

        $attestation->delete();

        return redirect()->route('backoffice.attestations.index')
            ->with('success', 'Attestation supprimée avec succès.');
    }

    /**
     * AJAX — Renvoie les niveaux suivis du groupe (Suivi niveau).
     */
    public function groupLevels(Group $group)
    {
        $group->loadMissing('site');

        $segments = GroupLevelFollowup::query()
            ->where('group_id', $group->id)
            ->orderBy('level_start_date')
            ->get(['level', 'level_start_date', 'level_end_date'])
            ->map(function ($f) {
                return [
                    'level'      => $f->level,
                    'start_date' => optional($f->level_start_date)->toDateString(),
                    'end_date'   => optional($f->level_end_date)->toDateString(),
                ];
            })
            ->values();

        return response()->json([
            'group' => [
                'id'                 => $group->id,
                'name'               => $group->name,
                'date_debut'         => optional($group->date_debut)->toDateString() ?? $group->date_debut,
                'date_fin'           => optional($group->date_fin)->toDateString() ?? $group->date_fin,
                'site_name'          => $group->site?->name,
                'site_city'          => $group->site?->city,
                'hours_per_session'  => $group->site?->getCourseDuration() ?? 2.5,
            ],
            'levels' => $segments,
        ]);
    }

    public function pdf(string $id)
    {
        $attestation = Attestation::with('group.site', 'site')->findOrFail($id);

        abort_unless(
            $this->userSeesAllSites() ||
            in_array((int) $attestation->site_id, auth()->user()->accessibleSiteIds(), true),
            403
        );

        // Bilingue par défaut → vue principale.
        // Mono-langue → vue dédiée (de / fr / en) qui mutualise un partial _content.
        $view = $attestation->language === 'de_fr'
            ? 'backoffice.attestations.pdf'
            : 'backoffice.attestations.pdf-single';

        $pdf = Pdf::loadView($view, [
                'attestation' => $attestation,
                'lang'        => $attestation->language,
            ])
            ->setPaper('a4')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', true);

        return $pdf->download('attestation-' . $attestation->attestation_number . '.pdf');
    }

    /**
     * Calcule les Unterrichtseinheiten (séances de 45 min)
     * en comptant uniquement les jours ouvrés (Lun–Ven).
     */
    public static function computeUnits(Carbon $start, Carbon $end, float $hoursPerSession): int
    {
        if ($end->lt($start)) {
            return 0;
        }

        $weekdays = 0;
        $cur = $start->copy()->startOfDay();
        $stop = $end->copy()->startOfDay();

        while ($cur->lte($stop)) {
            if (!$cur->isWeekend()) {
                $weekdays++;
            }
            $cur->addDay();
        }

        // Chaque jour = 1 séance ; chaque séance = $hoursPerSession heures
        // 1 unité pédagogique = 45 minutes
        $minutesTotal = $weekdays * $hoursPerSession * 60;

        return (int) round($minutesTotal / 45);
    }

    private function hydrate(array $data): array
    {
        $data['is_legacy'] = !empty($data['is_legacy']);

        // Étudiant ancien : pas de groupe à chercher, tout est saisi manuellement.
        if ($data['is_legacy']) {
            $data['group_id']          = null;
            $data['hours_per_session'] = 2.5;
            // site_id vient du formulaire en mode legacy.
            $data['site_id'] = isset($data['site_id']) ? (int) $data['site_id'] : null;
        } else {
            $group = Group::with('site')->findOrFail($data['group_id']);

            // hours_per_session reste enregistré pour référence/historique mais n'est plus utilisé pour calculer units_45min.
            $data['hours_per_session'] = $group->site?->getCourseDuration() ?? 2.5;

            // Le centre est dérivé du groupe — source de vérité.
            $data['site_id'] = $group->site_id;

            // Si la ville n'est pas définie, on tente le centre
            if (empty($data['city'])) {
                $data['city'] = $group->site?->city ?? '';
            }
        }

        // units_45min provient du formulaire (saisie manuelle) — on garantit juste un entier positif.
        $data['units_45min'] = isset($data['units_45min']) ? max(0, (int) $data['units_45min']) : 0;

        // Cours en cours : checkbox non cochée = false.
        $data['is_ongoing'] = !empty($data['is_ongoing']);

        return $data;
    }

    private function groupsForSelect()
    {
        $query = Group::query()
            ->with('site')
            ->orderBy('name');

        // Limit groups to centres the user can access.
        $this->scopeToUserSites($query);

        return $query->get(['id', 'name', 'site_id', 'level', 'date_debut', 'date_fin']);
    }
}
