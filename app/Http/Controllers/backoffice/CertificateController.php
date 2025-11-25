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
     * Written + Oral CONSTANT MAX VALUES (Seuils)
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
     * Display a listing of the resource.
     */
    public function index()
    {
        $certificates = Certificate::latest()->paginate(10);

        return view('backoffice.certificates.index', compact('certificates'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('backoffice.certificates.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCertificateRequest $request)
    {
        $data = $request->validated();

        /**
         * Calculate Written Total
         */
        $data['written_total'] =
              $data['reading_score']
            + $data['grammar_score']
            + $data['listening_score']
            + $data['writing_score'];

        /**
         * Calculate Oral Total
         */
        $data['oral_total'] =
              $data['presentation_score']
            + $data['discussion_score']
            + $data['problemsolving_score'];

        Certificate::create($data);

        return redirect()
            ->route('backoffice.certificates.index')
            ->with('success', 'Certificat ajouté avec succès.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $certificate = Certificate::findOrFail($id);

        return view('backoffice.certificates.show', compact('certificate'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $certificate = Certificate::findOrFail($id);

        return view('backoffice.certificates.edit', compact('certificate'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCertificateRequest $request, string $id)
    {
        $certificate = Certificate::findOrFail($id);

        $data = $request->validated();

        /**
         * Recalculate totals
         */
        $data['written_total'] =
              $data['reading_score']
            + $data['grammar_score']
            + $data['listening_score']
            + $data['writing_score'];

        $data['oral_total'] =
              $data['presentation_score']
            + $data['discussion_score']
            + $data['problemsolving_score'];

        $certificate->update($data);

        return redirect()
            ->route('backoffice.certificates.index')
            ->with('success', 'Certificat mis à jour avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $certificate = Certificate::findOrFail($id);

        $certificate->delete();

        return redirect()
            ->route('backoffice.certificates.index')
            ->with('success', 'Certificat supprimé avec succès.');
    }

    /**
     * Generate the PDF certificate.
     */
    public function pdf(string $id)
    {
        $certificate = Certificate::findOrFail($id);

        $pdf = Pdf::loadView('backoffice.certificates.pdf', compact('certificate'))
                  ->setPaper('a4');

        return $pdf->download('certificate-' . $certificate->certificate_number . '.pdf');
    }
}
