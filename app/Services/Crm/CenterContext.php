<?php

namespace App\Services\Crm;

use App\Models\Site;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;

/**
 * Resolves which CRM center (Homeschool store) the user is currently browsing.
 *
 * Sources, in priority order:
 *   1. ?strStoreId= in the URL (one-off override)
 *   2. session('crm.center_id') (persistent selection from the dropdown)
 *   3. null = "all centers" (let the API decide based on token scope)
 *
 * Centers come from the sites table, filtered to rows that have crm_store_id set.
 */
class CenterContext
{
    public const SESSION_KEY = 'crm.center_id';

    /**
     * All centers available for selection in the dropdown.
     *
     * @return Collection<int, Site>
     */
    public function available(): Collection
    {
        return Site::query()
            ->where('crm_store_id', '>', 0)
            ->orderBy('name')
            ->get(['id', 'name', 'city', 'crm_store_id']);
    }

    /**
     * The currently selected store id (or null = all).
     */
    public function currentStoreId(?int $urlOverride = null): ?int
    {
        if ($urlOverride !== null && $urlOverride > 0) {
            return $urlOverride;
        }

        $session = Session::get(self::SESSION_KEY);
        return $session ? (int) $session : null;
    }

    /**
     * Resolve the matching Site for the current selection (for display).
     */
    public function currentSite(?int $urlOverride = null): ?Site
    {
        $storeId = $this->currentStoreId($urlOverride);
        if (!$storeId) {
            return null;
        }
        return Site::where('crm_store_id', $storeId)->first();
    }

    /**
     * Persist a new selection. Passing null clears it ("all centers").
     */
    public function setStoreId(?int $storeId): void
    {
        if ($storeId) {
            Session::put(self::SESSION_KEY, $storeId);
        } else {
            Session::forget(self::SESSION_KEY);
        }
    }

    /**
     * Token to use for the currently selected center. Falls back to null
     * (= use the global CRM_API_TOKEN from .env) when no center is selected
     * or the center has no per-row token.
     */
    public function currentToken(?int $urlOverride = null): ?string
    {
        $site = $this->currentSite($urlOverride);
        return $site?->crm_token ?: null;
    }

    /**
     * Cached map of crm_store_id => site name for quick lookups inside Blade.
     * Used to render human-readable labels next to STR_STORE_ID columns in tables.
     *
     * @return array<int,string>
     */
    public function storeIdToName(): array
    {
        static $map = null;
        if ($map === null) {
            $map = $this->available()
                ->pluck('name', 'crm_store_id')
                ->toArray();
        }
        return $map;
    }

    public function nameForStoreId(int|string|null $storeId): ?string
    {
        if ($storeId === null || $storeId === '') {
            return null;
        }
        return $this->storeIdToName()[(int) $storeId] ?? null;
    }
}
