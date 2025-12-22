<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\Studienkolleg;
use App\Http\Requests\Backoffice\Studienkolleg\StoreStudienkollegRequest;
use App\Http\Requests\Backoffice\Studienkolleg\UpdateStudienkollegRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Str;

class StudienkollegController extends Controller
{
    /* =========================
     * INDEX
     * ========================= */
    public function index(): View
    {
        $studienkollegs = Studienkolleg::latest()->paginate(20);
        return view('backoffice.studienkollegs.index', compact('studienkollegs'));
    }

    /* =========================
     * CREATE
     * ========================= */
    public function create(): View
    {
        return view('backoffice.studienkollegs.create');
    }

    /* =========================
     * STORE
     * ========================= */
    public function store(StoreStudienkollegRequest $request): RedirectResponse
    {
        $studienkolleg = Studienkolleg::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'university' => $request->university,
            'city' => $request->city,
            'state' => $request->state,
            'country' => $request->country ?? 'Germany',

            'duration_semesters' => $request->duration_semesters,
            'tuition' => $request->tuition,
            'language_of_instruction' => $request->language_of_instruction,

            'featured' => $request->boolean('featured'),
            'public' => $request->boolean('public'),
            'uni_assist' => $request->boolean('uni_assist'),
            'entrance_exam' => $request->boolean('entrance_exam'),

            'courses' => $request->courses,
            'languages' => $this->linesToArray($request->languages),
            'documents' => $this->linesToArray($request->documents),

            'application_method' => $request->application_method,
            'application_portal_note' => $request->application_portal_note,
            'application_url' => $request->application_url,

            'exam_subjects' => $request->exam_subjects,
            'exam_link' => $request->exam_link,

            'certification_required' => $request->boolean('certification_required'),
            'translation_required' => $request->boolean('translation_required'),
            'translation_note' => $request->translation_note,

            'official_website' => $request->official_website,
            'contact_email' => $request->contact_email,
            'address' => $request->address,

            'map_embed' => $request->map_embed,
        ]);

        /* ========= MEDIA (BLOG STYLE) ========= */
        if ($request->hasFile('hero_image')) {
            $studienkolleg->addMediaFromRequest('hero_image')->toMediaCollection('studienkolleg_hero');
        }

        if ($request->hasFile('card_image')) {
            $studienkolleg->addMediaFromRequest('card_image')->toMediaCollection('studienkolleg_card');
        }

        if ($request->hasFile('university_logo')) {
            $studienkolleg->addMediaFromRequest('university_logo')->toMediaCollection('university_logo');
        }

        return redirect()->route('backoffice.studienkollegs.index')->with('success', 'Studienkolleg created successfully.');
    }

    /* =========================
     * EDIT
     * ========================= */
    public function edit(Studienkolleg $studienkolleg): View
    {
        return view('backoffice.studienkollegs.edit', compact('studienkolleg'));
    }

    /* =========================
     * UPDATE
     * ========================= */
    public function update(UpdateStudienkollegRequest $request, Studienkolleg $studienkolleg): RedirectResponse
    {
        $data = $this->prepareData($request, $studienkolleg);

        $studienkolleg->update($data);

        // ===== MEDIA (COMME BLOG) =====
        $this->handleMedia($request, $studienkolleg, true);

        return redirect()->route('backoffice.studienkollegs.index')->with('success', 'Studienkolleg updated successfully.');
    }

    /* =========================
     * DELETE
     * ========================= */
    public function destroy(Studienkolleg $studienkolleg): RedirectResponse
    {
        $studienkolleg->clearMediaCollection('studienkolleg_hero');
        $studienkolleg->clearMediaCollection('studienkolleg_card');
        $studienkolleg->clearMediaCollection('studienkolleg_logo');

        $studienkolleg->delete();

        return redirect()->route('backoffice.studienkollegs.index')->with('success', 'Studienkolleg deleted successfully.');
    }

    /* =====================================================
     * DATA PREPARATION
     * ===================================================== */
    private function prepareData($request, ?Studienkolleg $studienkolleg = null): array
    {
        $data = $request->validated();

        $data['slug'] = Str::slug($data['name']);

        $data['featured'] = $request->boolean('featured');
        $data['public'] = $request->boolean('public');
        $data['uni_assist'] = $request->boolean('uni_assist');
        $data['entrance_exam'] = $request->boolean('entrance_exam');

        $data['courses'] = $request->input('courses');
        $data['languages'] = $this->linesToArray($request->input('languages'));
        $data['documents'] = $this->linesToArray($request->input('documents'));

        // ✅ FIX MAP EMBED
        $data['map_embed'] = $request->input('map_embed');

        return $data;
    }

    /* =====================================================
     * MEDIA HANDLER (SPATIE)
     * ===================================================== */
    private function handleMedia($request, Studienkolleg $studienkolleg): void
    {
        if ($request->hasFile('hero_image')) {
            $studienkolleg->clearMediaCollection('studienkolleg_hero');
            $studienkolleg->addMediaFromRequest('hero_image')->toMediaCollection('studienkolleg_hero');
        }

        if ($request->hasFile('card_image')) {
            $studienkolleg->clearMediaCollection('studienkolleg_card');
            $studienkolleg->addMediaFromRequest('card_image')->toMediaCollection('studienkolleg_card');
        }

        if ($request->hasFile('university_logo')) {
            $studienkolleg->clearMediaCollection('university_logo');
            $studienkolleg->addMediaFromRequest('university_logo')->toMediaCollection('university_logo');
        }
    }

    /* =====================================================
     * UTIL
     * ===================================================== */
    private function linesToArray(string|array|null $value): ?array
    {
        if (is_array($value)) {
            return array_values(array_filter(array_map('trim', $value)));
        }

        if (is_string($value)) {
            return array_values(array_filter(array_map('trim', explode("\n", $value))));
        }

        return null;
    }
}
