<?php

namespace App\Http\Controllers\Backoffice\Crm;

use App\Http\Controllers\Controller;
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
    protected function render(string $viewName, \Closure $call, array $extra = []): View
    {
        $urlOverride = request()->filled('strStoreId') ? (int) request()->query('strStoreId') : null;
        $strStoreId  = $this->centers->currentStoreId($urlOverride);

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
        $urlOverride = request()->filled('strStoreId') ? (int) request()->query('strStoreId') : null;
        $token = $this->centers->currentToken($urlOverride);
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
        ], $data));
    }

    /**
     * Resolve the strStoreId for the current request — applies the optional
     * ?strStoreId= URL override, then falls back to the session-bound center.
     */
    protected function currentStrStoreId(): ?int
    {
        $urlOverride = request()->filled('strStoreId') ? (int) request()->query('strStoreId') : null;
        return $this->centers->currentStoreId($urlOverride);
    }
}
