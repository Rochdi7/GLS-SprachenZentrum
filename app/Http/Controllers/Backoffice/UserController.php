<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Concerns\ScopesToUserSites;
use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    use ScopesToUserSites;
    private const HIDDEN_EMAILS = [
        'rochdi.karouali1234@gmail.com',
    ];

    public function index()
    {
        $users = $this->scopeToUserSites(
            User::with(['roles', 'site', 'sites'])
                ->whereNotIn('email', self::HIDDEN_EMAILS)
        )->latest()->get();

        return view('backoffice.users.index', compact('users'));
    }

    public function create()
    {
        $roles = $this->availableRoles();
        $sites = $this->accessibleSites();
        $staffRoles = User::STAFF_ROLES;

        return view('backoffice.users.create', compact('roles', 'sites', 'staffRoles'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateUser($request);

        if ($validated['role'] === 'Super Admin' && ! auth()->user()->hasRole('Super Admin')) {
            return back()->with('error', 'Seul un Super Admin peut attribuer le rôle Super Admin.');
        }

        [$primarySiteId, $siteIds] = $this->resolveSites($validated);

        $user = User::create([
            'name'              => $validated['name'],
            'email'             => $validated['email'],
            'password'          => $validated['password'],
            'phone'             => $validated['phone'] ?? null,
            'site_id'           => $primarySiteId,
            'staff_role'        => $validated['staff_role'] ?? null,
            'hired_at'          => $validated['hired_at'] ?? null,
            'is_active'         => $request->boolean('is_active', true),
            'staff_notes'       => $validated['staff_notes'] ?? null,
            'email_verified_at' => now(),
        ]);

        $user->assignRole($validated['role']);
        $user->sites()->sync($siteIds);

        if ($request->hasFile('avatar')) {
            $user->addMediaFromRequest('avatar')->toMediaCollection('profile_photo');
        }

        return redirect()
            ->route('backoffice.users.index')
            ->with('success', 'Utilisateur créé avec succès.');
    }

    public function edit(string $id)
    {
        $user  = User::with('sites')->findOrFail($id);
        $roles = $this->availableRoles();
        $sites = $this->accessibleSites();
        $staffRoles = User::STAFF_ROLES;

        return view('backoffice.users.edit', compact('user', 'roles', 'sites', 'staffRoles'));
    }

    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);
        $validated = $this->validateUser($request, $user->id);

        if ($validated['role'] === 'Super Admin' && ! auth()->user()->hasRole('Super Admin')) {
            return back()->with('error', 'Seul un Super Admin peut attribuer le rôle Super Admin.');
        }

        [$primarySiteId, $siteIds] = $this->resolveSites($validated);

        $userData = [
            'name'        => $validated['name'],
            'email'       => $validated['email'],
            'phone'       => $validated['phone'] ?? null,
            'site_id'     => $primarySiteId,
            'staff_role'  => $validated['staff_role'] ?? null,
            'hired_at'    => $validated['hired_at'] ?? null,
            'is_active'   => $request->boolean('is_active'),
            'staff_notes' => $validated['staff_notes'] ?? null,
        ];

        if (! empty($validated['password'])) {
            $userData['password'] = $validated['password'];
        }

        $user->update($userData);
        $user->syncRoles([$validated['role']]);
        $user->sites()->sync($siteIds);

        if ($request->boolean('remove_avatar')) {
            $user->clearMediaCollection('profile_photo');
        } elseif ($request->hasFile('avatar')) {
            $user->addMediaFromRequest('avatar')->toMediaCollection('profile_photo');
        }

        return redirect()
            ->route('backoffice.users.index')
            ->with('success', 'Utilisateur mis à jour avec succès.');
    }

    public function destroy(string $id)
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            return redirect()
                ->route('backoffice.users.index')
                ->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        $user->delete();

        return redirect()
            ->route('backoffice.users.index')
            ->with('success', 'Utilisateur supprimé avec succès.');
    }

    private function validateUser(Request $request, ?int $userId = null): array
    {
        return $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => ['required', 'email', Rule::unique('users', 'email')->ignore($userId)],
            'password'    => $userId ? 'nullable|string|min:6|confirmed' : 'required|string|min:6|confirmed',
            'role'        => 'required|string|exists:roles,name',
            'phone'       => 'nullable|string|max:50',
            'site_id'     => 'nullable|exists:sites,id',
            'site_ids'    => 'nullable|array',
            'site_ids.*'  => 'integer|exists:sites,id',
            'staff_role'  => ['nullable', Rule::in(User::STAFF_ROLES)],
            'hired_at'    => 'nullable|date',
            'is_active'     => 'nullable|boolean',
            'staff_notes'   => 'nullable|string|max:2000',
            'avatar'        => 'nullable|image|max:2048',
            'remove_avatar' => 'nullable|boolean',
        ]);
    }

    /**
     * Reconcile the single primary `site_id` and the multi-centre `site_ids[]`.
     *
     * Behaviour: the union of both is what the user can access; the primary is
     * either the explicit `site_id` (if still present in the multi list) or
     * the first selected site. Returns [primarySiteId|null, list<int>].
     */
    private function resolveSites(array $validated): array
    {
        $multi = collect($validated['site_ids'] ?? [])
            ->map(fn ($v) => (int) $v)
            ->filter()
            ->unique()
            ->values();

        $primary = isset($validated['site_id']) && $validated['site_id'] !== null && $validated['site_id'] !== ''
            ? (int) $validated['site_id']
            : null;

        if ($primary === null && $multi->isNotEmpty()) {
            $primary = (int) $multi->first();
        }

        if ($primary !== null && ! $multi->contains($primary)) {
            $multi->push($primary);
        }

        return [$primary, $multi->all()];
    }

    private function availableRoles()
    {
        $query = Role::orderBy('name');

        if (! auth()->user()->hasRole('Super Admin')) {
            $query->where('name', '!=', 'Super Admin');
        }

        return $query->get();
    }
}
