<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Group;
use Carbon\Carbon;

class GroupApiController extends Controller
{
    /**
     * Retourne toutes les dates disponibles (jour par jour)
     * pour un site + niveau donnés.
     *
     * - Exclut automatiquement les weekends
     * - Découpe les périodes date_debut → date_fin
     */
    public function getDates($site_id, $level)
{
    // DEBUG : Afficher les groupes trouvés
    $groups = Group::where('site_id', $site_id)
        ->where('level', $level)
        ->get(['id','date_debut','date_fin']);

    if ($groups->isEmpty()) {
        return response()->json([
            'error' => 'No groups found for this site/level',
            'site_id' => $site_id,
            'level' => $level
        ]);
    }

    $days = [];

    foreach ($groups as $group) {

        if (!$group->date_debut || !$group->date_fin) {
            continue;
        }

        $start = Carbon::parse($group->date_debut);
        $end   = Carbon::parse($group->date_fin);

        while ($start->lte($end)) {
            if (!$start->isWeekend()) {
                $days[] = $start->format('Y-m-d');
            }
            $start->addDay();
        }
    }

    return response()->json($days);
}

}


