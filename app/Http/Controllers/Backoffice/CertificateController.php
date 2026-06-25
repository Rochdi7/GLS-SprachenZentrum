<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Concerns\ScopesToUserSites;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backoffice\Certificates\StoreCertificateRequest;
use App\Http\Requests\Backoffice\Certificates\UpdateCertificateRequest;
use App\Models\Certificate;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class CertificateController extends Controller
{
    use ScopesToUserSites;

    /**
     * Score configs per certificate type.
     */
    private const SCORE_CONFIGS = [
        'b2' => [
            'reading'        => 75,
            'grammar'        => 30,
            'listening'      => 75,
            'writing'        => 45,
            'presentation'   => 25,
            'discussion'     => 25,
            'problemsolving' => 25,
            'written_max'    => 225,
            'oral_max'       => 75,
        ],
        'a2' => [
            'reading'   => 25,
            'listening' => 25,
            'writing'   => 25,
            'speaking'  => 25,
        ],
        'a1' => [
            'reading'   => 15,
            'listening' => 15,
            'writing'   => 15,
            'speaking'  => 15,
        ],
        'b1' => [
            'reading'   => 60,
            'listening' => 60,
            'writing'   => 60,
            'speaking'  => 60,
        ],
    ];

    /**
     * INDEX
     */
    public function index(Request $request)
    {
        $query = Certificate::with('site')->latest();

        $this->scopeToUserSites($query);

        $requestedSiteId = $this->resolveRequestedSiteId(
            $request->filled('site_id') ? (int) $request->site_id : null
        );
        if ($requestedSiteId) {
            $query->where('site_id', $requestedSiteId);
        }

        $certificates = $query->get();
        $sites        = $this->accessibleSites();

        return view('backoffice.certificates.index', compact('certificates', 'sites', 'requestedSiteId'));
    }

    /**
     * CREATE VIEW
     */
    public function create()
    {
        return view('backoffice.certificates.create', [
            'scoreConfigs' => self::SCORE_CONFIGS,
            'sites'        => $this->accessibleSites(),
        ]);
    }

    /**
     * STORE CERTIFICATE
     */
    public function store(StoreCertificateRequest $request)
    {
        $data = $this->hydrateScores($request->validated());

        abort_unless(
            $this->userSeesAllSites() ||
            in_array((int) $data['site_id'], auth()->user()->accessibleSiteIds(), true),
            403,
            "Vous n'avez pas accès à ce centre."
        );

        Certificate::create($data);

        return redirect()->route('backoffice.certificates.index')->with('success', 'Certificat ajouté avec succès.');
    }

    /**
     * SHOW
     */
    public function show(string $id)
    {
        $certificate = Certificate::findOrFail($id);

        abort_unless(
            $this->userSeesAllSites() ||
            in_array((int) $certificate->site_id, auth()->user()->accessibleSiteIds(), true),
            403
        );

        return view('backoffice.certificates.show', compact('certificate'));
    }

    /**
     * EDIT
     */
    public function edit(string $id)
    {
        $certificate = Certificate::findOrFail($id);

        abort_unless(
            $this->userSeesAllSites() ||
            in_array((int) $certificate->site_id, auth()->user()->accessibleSiteIds(), true),
            403
        );

        return view('backoffice.certificates.edit', [
            'certificate'  => $certificate,
            'scoreConfigs' => self::SCORE_CONFIGS,
            'sites'        => $this->accessibleSites(),
        ]);
    }

    /**
     * UPDATE CERTIFICATE
     */
    public function update(UpdateCertificateRequest $request, string $id)
    {
        $certificate = Certificate::findOrFail($id);

        abort_unless(
            $this->userSeesAllSites() ||
            in_array((int) $certificate->site_id, auth()->user()->accessibleSiteIds(), true),
            403
        );

        $data = $this->hydrateScores($request->validated());

        abort_unless(
            $this->userSeesAllSites() ||
            in_array((int) $data['site_id'], auth()->user()->accessibleSiteIds(), true),
            403,
            "Vous n'avez pas accès à ce centre."
        );

        $certificate->update($data);

        return redirect()->route('backoffice.certificates.index')->with('success', 'Certificat mis à jour avec succès.');
    }

    /**
     * DELETE CERTIFICATE
     */
    public function destroy(string $id)
    {
        $certificate = Certificate::findOrFail($id);

        abort_unless(
            $this->userSeesAllSites() ||
            in_array((int) $certificate->site_id, auth()->user()->accessibleSiteIds(), true),
            403
        );

        $certificate->delete();

        return redirect()->route('backoffice.certificates.index')->with('success', 'Certificat supprimé avec succès.');
    }

    /**
     * PDF EXPORT + QR CODE
     */
    public function pdf(string $id)
    {
        $certificate = Certificate::findOrFail($id);

        abort_unless(
            $this->userSeesAllSites() ||
            in_array((int) $certificate->site_id, auth()->user()->accessibleSiteIds(), true),
            403
        );

        $url = route('certificates.public.download', [
            'token' => $certificate->public_token,
        ]);

        $qrSvg = QrCode::format('svg')
            ->size(180)
            ->margin(1)
            ->generate($url);

        $qrCodeBase64 = base64_encode($qrSvg);

        $pdf = Pdf::loadView('backoffice.certificates.pdf', [
                'certificate' => $certificate,
                'qrCodeBase64' => $qrCodeBase64,
                'qrUrl' => $url,
            ])
            ->setPaper('a4')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', true);

        return $pdf->download('certificate-' . $certificate->certificate_number . '.pdf');
    }

    /**
     * ---------------------------------------------------------
     *   SCORE NORMALIZATION — type-aware (A2 vs B2)
     * ---------------------------------------------------------
     */
    private function hydrateScores(array $data): array
    {
        $type   = $data['certificate_type'] ?? 'b2';
        $config = self::SCORE_CONFIGS[$type] ?? self::SCORE_CONFIGS['b2'];

        if (in_array($type, ['a1', 'a2', 'b1'], true)) {
            return $this->hydrateA2($data, $config);
        }

        return $this->hydrateB2($data, $config);
    }

    private function hydrateA2(array $data, array $config): array
    {
        $data['reading_max'] = $config['reading'];
        $data['listening_max'] = $config['listening'];
        $data['writing_max'] = $config['writing'];
        $data['speaking_max'] = $config['speaking'];

        $data['reading_score'] = min($data['reading_score'], $config['reading']);
        $data['listening_score'] = min($data['listening_score'], $config['listening']);
        $data['writing_score'] = min($data['writing_score'], $config['writing']);
        $data['speaking_score'] = min($data['speaking_score'], $config['speaking']);

        // Nullify B2-only fields
        $data['grammar_score'] = null;
        $data['grammar_max'] = null;
        $data['presentation_score'] = null;
        $data['presentation_max'] = null;
        $data['discussion_score'] = null;
        $data['discussion_max'] = null;
        $data['problemsolving_score'] = null;
        $data['problemsolving_max'] = null;
        $data['written_total'] = null;
        $data['written_max'] = null;
        $data['oral_total'] = null;
        $data['oral_max'] = null;

        return $data;
    }

    private function hydrateB2(array $data, array $config): array
    {
        $data['reading_max'] = $config['reading'];
        $data['grammar_max'] = $config['grammar'];
        $data['listening_max'] = $config['listening'];
        $data['writing_max'] = $config['writing'];

        $data['presentation_max'] = $config['presentation'];
        $data['discussion_max'] = $config['discussion'];
        $data['problemsolving_max'] = $config['problemsolving'];

        $data['written_max'] = $config['written_max'];
        $data['oral_max'] = $config['oral_max'];

        $data['reading_score'] = min($data['reading_score'], $config['reading']);
        $data['grammar_score'] = min($data['grammar_score'], $config['grammar']);
        $data['listening_score'] = min($data['listening_score'], $config['listening']);
        $data['writing_score'] = min($data['writing_score'], $config['writing']);

        $data['presentation_score'] = min($data['presentation_score'], $config['presentation']);
        $data['discussion_score'] = min($data['discussion_score'], $config['discussion']);
        $data['problemsolving_score'] = min($data['problemsolving_score'], $config['problemsolving']);

        $data['written_total'] = $data['reading_score'] + $data['grammar_score']
                               + $data['listening_score'] + $data['writing_score'];

        $data['oral_total'] = $data['presentation_score'] + $data['discussion_score']
                            + $data['problemsolving_score'];

        // Nullify A2-only fields
        $data['speaking_score'] = null;
        $data['speaking_max'] = null;

        return $data;
    }
}
