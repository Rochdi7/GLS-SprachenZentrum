<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backoffice\Certificates\StoreCertificateRequest;
use App\Http\Requests\Backoffice\Certificates\UpdateCertificateRequest;
use App\Models\Certificate;
use Barryvdh\DomPDF\Facade\Pdf;

class CertificateController extends Controller
{
    /**
     * ---------------------------------------------------------
     *   SEUIL NOTES (MAX SCORES) — KEEP EXACTLY AS USER WANTS
     * ---------------------------------------------------------
     */
    private const READING_MAX = 75;
    private const GRAMMAR_MAX = 30;
    private const LISTENING_MAX = 75;
    private const WRITING_MAX = 45;

    private const PRESENTATION_MAX = 25;
    private const DISCUSSION_MAX = 25;
    private const PROBLEMSOLVING_MAX = 25;

    private const WRITTEN_MAX = 225;
    private const ORAL_MAX = 75;

    /**
     * INDEX
     */
    public function index()
    {
        $certificates = Certificate::latest()->paginate(12);

        return view('backoffice.certificates.index', compact('certificates'));
    }

    /**
     * CREATE VIEW
     */
    public function create()
    {
        return view('backoffice.certificates.create', [
            'max' => [
                'reading' => self::READING_MAX,
                'grammar' => self::GRAMMAR_MAX,
                'listening' => self::LISTENING_MAX,
                'writing' => self::WRITING_MAX,

                'presentation' => self::PRESENTATION_MAX,
                'discussion' => self::DISCUSSION_MAX,
                'problemsolving' => self::PROBLEMSOLVING_MAX,
            ],
        ]);
    }

    /**
     * STORE CERTIFICATE
     */
    public function store(StoreCertificateRequest $request)
    {
        $data = $this->hydrateScores($request->validated());

        Certificate::create($data);

        return redirect()->route('backoffice.certificates.index')->with('success', 'Certificat ajouté avec succès.');
    }

    /**
     * SHOW
     */
    public function show(string $id)
    {
        $certificate = Certificate::findOrFail($id);

        return view('backoffice.certificates.show', compact('certificate'));
    }

    /**
     * EDIT
     */
    public function edit(string $id)
    {
        $certificate = Certificate::findOrFail($id);

        return view('backoffice.certificates.edit', [
            'certificate' => $certificate,
            'max' => [
                'reading' => self::READING_MAX,
                'grammar' => self::GRAMMAR_MAX,
                'listening' => self::LISTENING_MAX,
                'writing' => self::WRITING_MAX,

                'presentation' => self::PRESENTATION_MAX,
                'discussion' => self::DISCUSSION_MAX,
                'problemsolving' => self::PROBLEMSOLVING_MAX,
            ],
        ]);
    }

    /**
     * UPDATE CERTIFICATE
     */
    public function update(UpdateCertificateRequest $request, string $id)
    {
        $certificate = Certificate::findOrFail($id);

        $data = $this->hydrateScores($request->validated());

        $certificate->update($data);

        return redirect()->route('backoffice.certificates.index')->with('success', 'Certificat mis à jour avec succès.');
    }

    /**
     * DELETE CERTIFICATE
     */
    public function destroy(string $id)
    {
        Certificate::findOrFail($id)->delete();

        return redirect()->route('backoffice.certificates.index')->with('success', 'Certificat supprimé avec succès.');
    }

    /**
     * PDF EXPORT
     */
    public function pdf(string $id)
{
    $certificate = Certificate::findOrFail($id);

    $pdf = Pdf::loadView('backoffice.certificates.pdf', compact('certificate'))
        ->setPaper('a4')
        ->setOption('isHtml5ParserEnabled', true)
        ->setOption('isRemoteEnabled', true);

    return $pdf->download('certificate-' . $certificate->certificate_number . '.pdf');
}


    /**
     * ---------------------------------------------------------
     *               SCORE NORMALIZATION + MAX VALUES
     * ---------------------------------------------------------
     * Fills max values, blocks scores > max, calculates totals.
     */
    private function hydrateScores(array $data): array
    {
        // Attach MAX VALUES
        $data['reading_max'] = self::READING_MAX;
        $data['grammar_max'] = self::GRAMMAR_MAX;
        $data['listening_max'] = self::LISTENING_MAX;
        $data['writing_max'] = self::WRITING_MAX;

        $data['presentation_max'] = self::PRESENTATION_MAX;
        $data['discussion_max'] = self::DISCUSSION_MAX;
        $data['problemsolving_max'] = self::PROBLEMSOLVING_MAX;

        $data['written_max'] = self::WRITTEN_MAX;
        $data['oral_max'] = self::ORAL_MAX;

        // Normalize scores: NEVER allow score > max
        $data['reading_score'] = min($data['reading_score'], self::READING_MAX);
        $data['grammar_score'] = min($data['grammar_score'], self::GRAMMAR_MAX);
        $data['listening_score'] = min($data['listening_score'], self::LISTENING_MAX);
        $data['writing_score'] = min($data['writing_score'], self::WRITING_MAX);

        $data['presentation_score'] = min($data['presentation_score'], self::PRESENTATION_MAX);
        $data['discussion_score'] = min($data['discussion_score'], self::DISCUSSION_MAX);
        $data['problemsolving_score'] = min($data['problemsolving_score'], self::PROBLEMSOLVING_MAX);

        // Calculate totals
        $data['written_total'] = $data['reading_score'] + $data['grammar_score'] + $data['listening_score'] + $data['writing_score'];

        $data['oral_total'] = $data['presentation_score'] + $data['discussion_score'] + $data['problemsolving_score'];

        return $data;
    }
}
