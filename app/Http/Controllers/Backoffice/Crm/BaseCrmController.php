<?php

namespace App\Http\Controllers\Backoffice\Crm;

use App\Http\Controllers\Controller;
use App\Models\CrmSyncLog;
use App\Services\Crm\CenterContext;
use App\Services\Crm\Crm;
use App\Services\Crm\CrmException;
use App\Services\Crm\CrmLovProvider;
use Illuminate\View\View;

/**
 * Shared base for all CRM backoffice controllers.
 *
 * Centralises:
 *   - Dependency injection of Crm + CenterContext + CrmLovProvider
 *   - The active-center resolution flow (strStoreId override → currentStoreId)
 *   - The "render with CrmException catch" pattern used by every list page
 *   - view() that always seeds the center dropdown + active site
 *
 * Children are expected to keep methods short — one HTTP action per method,
 * delegating heavy lifting to Service classes under App\Services\Crm.
 */
abstract class BaseCrmController extends Controller
{
    public function __construct(
        protected Crm $crm,
        protected CenterContext $centers,
        protected CrmLovProvider $lovs,
    ) {
    }

    /**
     * Render a CRM page with center context.
     *
     * Resolves the active strStoreId, runs the API call, catches CrmException,
     * and hands the view a uniform bundle: payload, error, filters, centers,
     * currentStoreId.
     *
     * @param  \Closure(?int $strStoreId): array<string,mixed>  $call
     */
    /**
     * Resolve strStoreId from the URL.
     * If ?strStoreId= is present but empty → explicitly means "all centers" (bypass session).
     * If ?strStoreId= is absent → fall back to session.
     */
    private function resolveUrlStoreId(): ?int
    {
        if (!request()->has('strStoreId')) {
            return null; // not in URL → let CenterContext use session
        }
        $val = request()->query('strStoreId');
        return ($val !== null && $val !== '') ? (int) $val : -1; // -1 = explicit "all"
    }

    protected function render(string $viewName, \Closure $call, array $extra = []): View
    {
        $raw        = $this->resolveUrlStoreId();
        $urlOverride = ($raw !== null && $raw > 0) ? $raw : null;
        // If strStoreId was explicitly set to empty in URL, bypass session entirely
        $strStoreId = ($raw === -1) ? null : $this->centers->currentStoreId($urlOverride);

        $payload = null;
        $error   = null;

        try {
            $payload = $call($strStoreId);
        } catch (CrmException $e) {
            $error = $e;
        }

        return $this->view($viewName, array_merge([
            'payload' => $payload,
            'error'   => $error,
            'filters' => request()->query(),
        ], $extra));
    }

    /**
     * Get a Crm instance scoped to the active center's token. Falls back to
     * the default Crm (using CRM_API_TOKEN from .env) when no per-center
     * token is configured.
     */
    protected function scopedCrm(): Crm
    {
        $raw         = $this->resolveUrlStoreId();
        $urlOverride = ($raw !== null && $raw > 0) ? $raw : null;
        $token       = $this->centers->currentToken($urlOverride);
        return $this->crm->withToken($token);
    }

    /**
     * Wrap view() so every CRM page gets the center dropdown data without
     * each method needing to remember to pass it.
     */
    protected function view(string $name, array $data = []): View
    {
        return view($name, array_merge([
            'crmCenters'      => $this->centers->available(),
            'crmCurrentStore' => $this->centers->currentStoreId(),
            'crmCurrentSite'  => $this->centers->currentSite(),
            'crmLastSync'     => $this->lastSyncAt(),
        ], $data));
    }

    private function lastSyncAt(): ?\Carbon\Carbon
    {
        $max = CrmSyncLog::where('status', 'done')
            ->whereNotNull('completed_at')
            ->max('completed_at');

        return $max
            ? \Carbon\Carbon::parse($max)->setTimezone('Africa/Casablanca')
            : null;
    }

    /**
     * Resolve the strStoreId for the current request — applies the optional
     * ?strStoreId= URL override, then falls back to the session-bound center.
     */
    protected function currentStrStoreId(): ?int
    {
        $raw         = $this->resolveUrlStoreId();
        $urlOverride = ($raw !== null && $raw > 0) ? $raw : null;
        if ($raw === -1) return null; // explicit "all centers"
        return $this->centers->currentStoreId($urlOverride);
    }
}
