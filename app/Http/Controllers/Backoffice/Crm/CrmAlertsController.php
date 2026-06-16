<?php

namespace App\Http\Controllers\Backoffice\Crm;

use App\Models\CrmAlert;
use App\Services\Crm\Stats\AlertsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;

class CrmAlertsController extends BaseCrmController
{
    /**
     * GET /backoffice/crm/alerts
     */
    public function index(Request $r, AlertsService $svc): View
    {
        $storeId  = $this->currentStrStoreId();
        $status   = $r->query('status', 'open');
        $type     = $r->query('type');
        $severity = $r->query('severity');

        $data = $svc->summary(
            storeId : $storeId,
            status  : $status,
            type    : $type,
            severity: $severity,
            perPage : 25,
        );

        $lastAlert = CrmAlert::latest('detected_at')->value('detected_at');

        return $this->view('backoffice.crm.alerts.index', [
            'summary'    => $data,
            'storeId'    => $storeId,
            'filterStatus'   => $status,
            'filterType'     => $type,
            'filterSeverity' => $severity,
            'lastDetected'   => $lastAlert,
            'alertTypes'     => CrmAlert::ALERT_TYPE_LABELS,
            'severities'     => CrmAlert::SEVERITIES,
        ]);
    }

    /**
     * POST /backoffice/crm/alerts/{alert}/acknowledge
     */
    public function acknowledge(Request $r, CrmAlert $alert): RedirectResponse
    {
        if (in_array($alert->status, ['open', 'in_progress'])) {
            $alert->update([
                'status'      => 'in_progress',
                'resolved_by' => auth()->user()?->name,
            ]);
        }

        return back()->with('success', "Alerte marquée en cours de traitement.");
    }

    /**
     * POST /backoffice/crm/alerts/{alert}/resolve
     */
    public function resolve(Request $r, CrmAlert $alert): RedirectResponse
    {
        $alert->update([
            'status'      => 'resolved',
            'resolved_by' => auth()->user()?->name,
            'resolved_at' => now(),
        ]);

        return back()->with('success', "Alerte résolue.");
    }

    /**
     * POST /backoffice/crm/alerts/{alert}/dismiss
     */
    public function dismiss(Request $r, CrmAlert $alert): RedirectResponse
    {
        $alert->update([
            'status'      => 'dismissed',
            'resolved_by' => auth()->user()?->name,
            'resolved_at' => now(),
        ]);

        return back()->with('success', "Alerte ignorée.");
    }

    /**
     * POST /backoffice/crm/alerts/generate
     * Manually trigger detection for the current center.
     */
    public function generate(Request $r): RedirectResponse
    {
        $storeId = $this->currentStrStoreId();

        $args = ['--prune' => true];
        if ($storeId) {
            $args['--store'] = [$storeId];
        }

        Artisan::call('crm:generate-alerts', $args);

        return back()->with('success', "Détection des alertes lancée avec succès.");
    }
}
