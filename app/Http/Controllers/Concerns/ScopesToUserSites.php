<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Site;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;

/**
 * Centralised "show only what this user is affected to" rules.
 *
 * Roles allowed to see EVERY centre: Super Admin, Admin.
 * All other roles: limited to centres in `users.site_id` + `site_user` pivot.
 *
 * Applied to controllers that list/filter centre-scoped data so the rules stay
 * consistent across the app.
 */
trait ScopesToUserSites
{
    /**
     * True when this user can see all centres (no scoping needed).
     */
    protected function userSeesAllSites(?User $user = null): bool
    {
        $user = $user ?: auth()->user();
        if (! $user) return false;
        return $user->hasAnyRole(['Super Admin', 'Admin']);
    }

    /**
     * Centre IDs this user is allowed to access. Returns null when they see
     * every centre (i.e. no `whereIn` needed). Returns an empty array when
     * the user has no centre assigned — callers must treat this as
     * "show nothing".
     */
    protected function accessibleSiteIds(?User $user = null): ?array
    {
        $user = $user ?: auth()->user();
        if (! $user) return [];
        if ($this->userSeesAllSites($user)) return null;
        return $user->accessibleSiteIds();
    }

    /**
     * Sites the user can see in dropdowns/filters. Active sites only.
     */
    protected function accessibleSites(?User $user = null): Collection
    {
        $user = $user ?: auth()->user();
        $query = Site::where('is_active', true);

        if (! $this->userSeesAllSites($user)) {
            $ids = $user ? $user->accessibleSiteIds() : [];
            $query->whereIn('id', $ids ?: [0]); // [0] forces empty result when no centre
        }

        return $query->orderBy('name')->get();
    }

    /**
     * Apply the centre filter to an Eloquent / Query builder.
     *
     * - Super Admin / Admin → no-op.
     * - Others with centres → whereIn(column, accessibleSiteIds()).
     * - Others with no centre → forces an impossible filter (empty result).
     */
    protected function scopeToUserSites($query, string $column = 'site_id', ?User $user = null)
    {
        $user = $user ?: auth()->user();
        if ($this->userSeesAllSites($user)) {
            return $query;
        }

        $ids = $user ? $user->accessibleSiteIds() : [];
        if (empty($ids)) {
            // Force empty result rather than show everything.
            return $query->whereRaw('1 = 0');
        }

        return $query->whereIn($column, $ids);
    }

    /**
     * Resolve a user-supplied `site_id` filter, validating it's accessible.
     * Returns null when the requested id is invalid / not accessible.
     */
    protected function resolveRequestedSiteId(?int $requested, ?User $user = null): ?int
    {
        if ($requested === null) return null;
        $user = $user ?: auth()->user();
        if ($this->userSeesAllSites($user)) {
            return $requested;
        }
        $ids = $user ? $user->accessibleSiteIds() : [];
        return in_array((int) $requested, $ids, true) ? (int) $requested : null;
    }
}
