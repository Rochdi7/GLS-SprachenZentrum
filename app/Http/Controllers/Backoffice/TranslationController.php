<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Mail\TranslationReadyMail;
use App\Models\Translation;
use App\Models\TranslationItem;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TranslationController extends Controller
{
    /* ------------------------------------------------------------------ */
    /*  LIST                                                              */
    /* ------------------------------------------------------------------ */
    public function index(Request $request)
    {
        $status = $request->query('status');
        $q      = trim((string) $request->query('q', ''));

        $query = Translation::query()
            ->with('items.media')
            ->latest('date_received')
            ->latest('id');

        if (in_array($status, [Translation::STATUS_PENDING, Translation::STATUS_TRANSLATOR, Translation::STATUS_DELIVERED], true)) {
            $query->where('status', $status);
        }

        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('cin', 'like', "%{$q}%")
                  ->orWhere('student_name', 'like', "%{$q}%")
                  ->orWhere('phone', 'like', "%{$q}%")
                  ->orWhereHas('items', fn ($i) => $i->where('doc_type', 'like', "%{$q}%"));
            });
        }

        $translations = $query->get();

        $counts = [
            'all'        => Translation::count(),
            'pending'    => Translation::where('status', Translation::STATUS_PENDING)->count(),
            'translator' => Translation::where('status', Translation::STATUS_TRANSLATOR)->count(),
            'delivered'  => Translation::where('status', Translation::STATUS_DELIVERED)->count(),
        ];

        $grandTotal = (int) $translations->sum('total_cost');

        $defaultPrice = 0;

        return view('backoffice.translations.index', [
            'translations'  => $translations,
            'currentStatus' => $status,
            'q'             => $q,
            'counts'        => $counts,
            'grandTotal'    => $grandTotal,
            'defaultPrice'  => $defaultPrice,
        ]);
    }

    /* ------------------------------------------------------------------ */
    /*  CREATE / STORE                                                    */
    /* ------------------------------------------------------------------ */
    public function create()
    {
        return view('backoffice.translations.create', [
            'defaultPrice' => 0,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateOrder($request, creating: true);

        DB::transaction(function () use ($data, $request) {
            $translation = Translation::create([
                'cin'              => Translation::normalizeCin($data['cin']),
                'student_name'     => $data['student_name'],
                'phone'            => $data['phone']            ?? null,
                'email'            => $data['email']            ?? null,
                'date_received'    => $data['date_received']    ?? now()->toDateString(),
                'date_handed_over' => $data['date_handed_over'] ?? null,
                'status'           => $data['status']           ?? Translation::STATUS_PENDING,
                'notes'            => $data['notes']            ?? null,
            ]);

            foreach ($data['items'] as $idx => $item) {
                $created = $translation->items()->create([
                    'doc_type'       => $item['doc_type'] ?: 'Documents divers',
                    'page_count'     => (int) $item['page_count'],
                    'price_per_page' => (int) $item['price_per_page'],
                ]);

                $this->attachScans($created, $request->file("items.{$idx}.scans"));
            }

            $translation->recalculateTotal();
        });

        return redirect()->route('backoffice.translations.index')
            ->with('toast', 'Commande de traduction enregistrée.');
    }

    /* ------------------------------------------------------------------ */
    /*  EDIT / UPDATE                                                     */
    /* ------------------------------------------------------------------ */
    public function edit(Translation $translation)
    {
        $translation->load('items.media');

        return view('backoffice.translations.edit', [
            'translation' => $translation,
        ]);
    }

    public function update(Request $request, Translation $translation)
    {
        $data = $this->validateOrder($request, creating: false);

        DB::transaction(function () use ($data, $translation, $request) {
            $orderPayload = [
                'cin'              => Translation::normalizeCin($data['cin']),
                'student_name'     => $data['student_name'],
                'phone'            => $data['phone']            ?? null,
                'email'            => $data['email']            ?? null,
                'date_received'    => $data['date_received']    ?? null,
                'date_handed_over' => $data['date_handed_over'] ?? null,
                'status'           => $data['status'],
                'notes'            => $data['notes']            ?? null,
            ];

            if ($orderPayload['status'] === Translation::STATUS_DELIVERED && empty($orderPayload['date_handed_over'])) {
                $orderPayload['date_handed_over'] = now()->toDateString();
            }

            $translation->update($orderPayload);

            // Sync items: keep existing where id provided, update them; create new; delete missing.
            $keepIds = [];
            foreach ($data['items'] as $idx => $row) {
                $itemPayload = [
                    'doc_type'       => $row['doc_type'] ?: 'Documents divers',
                    'page_count'     => (int) $row['page_count'],
                    'price_per_page' => (int) $row['price_per_page'],
                ];

                $item = null;
                if (!empty($row['id'])) {
                    $item = $translation->items()->where('id', $row['id'])->first();
                    if ($item) {
                        $item->update($itemPayload);
                        $keepIds[] = $item->id;
                    }
                }

                if (!$item) {
                    $item = $translation->items()->create($itemPayload);
                    $keepIds[] = $item->id;
                }

                $this->attachScans($item, $request->file("items.{$idx}.scans"));
            }

            // Remove scans the user marked for deletion.
            if (!empty($data['remove_scans'])) {
                Media::whereIn('id', $data['remove_scans'])
                    ->where('model_type', TranslationItem::class)
                    ->whereIn('model_id', $keepIds)
                    ->each(fn (Media $m) => $m->delete());
            }

            $translation->items()->whereNotIn('id', $keepIds)->delete();

            $translation->recalculateTotal();
        });

        $toast = 'Commande mise à jour.';
        if ($translation->refresh()->status === Translation::STATUS_DELIVERED
            && $this->notifyReadyIfNeeded($translation)) {
            $toast = 'Commande mise à jour — l\'étudiant a été notifié par email.';
        }

        return redirect()->route('backoffice.translations.index')
            ->with('toast', $toast);
    }

    /* ------------------------------------------------------------------ */
    /*  STATUS / HANDOVER / DELETE                                        */
    /* ------------------------------------------------------------------ */
    public function updateStatus(Translation $translation)
    {
        $next = match ($translation->status) {
            Translation::STATUS_PENDING    => Translation::STATUS_TRANSLATOR,
            Translation::STATUS_TRANSLATOR => Translation::STATUS_DELIVERED,
            default                        => Translation::STATUS_PENDING,
        };

        $payload = ['status' => $next];

        if ($next === Translation::STATUS_DELIVERED && !$translation->date_handed_over) {
            $payload['date_handed_over'] = now()->toDateString();
        }

        $translation->update($payload);

        $toast = 'Statut mis à jour.';
        if ($next === Translation::STATUS_DELIVERED && $this->notifyReadyIfNeeded($translation)) {
            $toast = 'Statut mis à jour — l\'étudiant a été notifié par email.';
        }

        return back()->with('toast', $toast);
    }

    /**
     * Send the "papers are ready" email once, when an order reaches the
     * delivered status and has a student email and hasn't been notified yet.
     */
    private function notifyReadyIfNeeded(Translation $translation): bool
    {
        if ($translation->status !== Translation::STATUS_DELIVERED
            || empty($translation->email)
            || $translation->ready_notified_at !== null) {
            return false;
        }

        try {
            $translation->loadMissing('items');
            Mail::to($translation->email)->send(new TranslationReadyMail($translation));
            $translation->forceFill(['ready_notified_at' => now()])->saveQuietly();

            return true;
        } catch (\Throwable $e) {
            Log::error('TranslationReadyMail failed', [
                'translation_id' => $translation->id,
                'error'          => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function updateHandover(Request $request, Translation $translation)
    {
        $data = $request->validate([
            'date_handed_over' => ['nullable', 'date'],
        ]);

        $translation->update($data);

        return back();
    }

    public function destroy(Translation $translation)
    {
        $translation->delete();

        return back()->with('toast', 'Commande supprimée.');
    }

    /* ------------------------------------------------------------------ */
    /*  EXPORT                                                            */
    /* ------------------------------------------------------------------ */
    public function exportCsv(): StreamedResponse
    {
        $rows = Translation::with('items')
            ->latest('date_received')
            ->latest('id')
            ->get();

        $filename = 'GLS_Traductions_' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($out, ['CIN', 'Étudiant', 'Téléphone', 'Email', 'Document', 'Pages', 'Prix/page', 'Total ligne (DH)', 'Date Dépôt', 'Date Remise', 'Statut'], ';');
            foreach ($rows as $r) {
                if ($r->items->isEmpty()) {
                    fputcsv($out, [
                        $r->cin, $r->student_name, $r->phone, $r->email,
                        '-', 0, 0, 0,
                        optional($r->date_received)->format('d/m/Y'),
                        optional($r->date_handed_over)->format('d/m/Y'),
                        Translation::statuses()[$r->status] ?? $r->status,
                    ], ';');
                    continue;
                }
                foreach ($r->items as $i) {
                    fputcsv($out, [
                        $r->cin, $r->student_name, $r->phone, $r->email,
                        $i->doc_type, $i->page_count, $i->price_per_page, $i->line_total,
                        optional($r->date_received)->format('d/m/Y'),
                        optional($r->date_handed_over)->format('d/m/Y'),
                        Translation::statuses()[$r->status] ?? $r->status,
                    ], ';');
                }
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /* ------------------------------------------------------------------ */
    /*  SCANS                                                             */
    /* ------------------------------------------------------------------ */

    /**
     * Attach uploaded scan files to a translation item's "originals" collection.
     *
     * @param  array<int, UploadedFile>|UploadedFile|null  $files
     */
    private function attachScans(TranslationItem $item, $files): void
    {
        if (empty($files)) {
            return;
        }

        foreach (is_array($files) ? $files : [$files] as $file) {
            if ($file instanceof UploadedFile && $file->isValid()) {
                $item->addMedia($file)->toMediaCollection('originals');
            }
        }
    }

    /* ------------------------------------------------------------------ */
    /*  VALIDATION                                                        */
    /* ------------------------------------------------------------------ */
    private function validateOrder(Request $request, bool $creating): array
    {
        return $request->validate([
            'cin'                  => ['required', 'string', 'max:32'],
            'student_name'         => ['required', 'string', 'max:255'],
            'phone'                => ['nullable', 'string', 'max:32'],
            'email'                => ['nullable', 'email', 'max:255'],
            'date_received'        => ['nullable', 'date'],
            'date_handed_over'     => ['nullable', 'date'],
            'status'               => [$creating ? 'nullable' : 'required', 'in:pending,translator,delivered'],
            'notes'                => ['nullable', 'string', 'max:5000'],

            'items'                  => ['required', 'array', 'min:1'],
            'items.*.id'             => ['nullable', 'integer'],
            'items.*.doc_type'       => ['nullable', 'string', 'max:255'],
            'items.*.page_count'     => ['required', 'integer', 'min:1'],
            'items.*.price_per_page' => ['required', 'integer', 'min:0'],
            'items.*.scans'          => ['nullable', 'array'],
            'items.*.scans.*'        => ['file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:10240'],
            'remove_scans'           => ['nullable', 'array'],
            'remove_scans.*'         => ['integer'],
        ], [
            'items.required'      => 'Vous devez ajouter au moins un document.',
            'items.min'           => 'Vous devez ajouter au moins un document.',
            'items.*.scans.*.mimes' => 'Les scans doivent être au format PDF, JPG, PNG ou WEBP.',
            'items.*.scans.*.max'   => 'Chaque scan ne doit pas dépasser 10 Mo.',
        ]);
    }
}
