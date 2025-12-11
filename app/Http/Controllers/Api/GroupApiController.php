<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Group;
use Carbon\Carbon;

class GroupApiController extends Controller
{
    /**
     * Retourne toutes les dates disponibles (jour par jour)
     * pour un groupe donné.
     *
     * - Exclut automatiquement les weekends
     * - Découpe date_debut → date_fin
     */
    public function getDates($group_id)
    {
        $group = Group::find($group_id);

        if (!$group || !$group->date_debut || !$group->date_fin) {
            return response()->json([]);
        }

        $start = Carbon::parse($group->date_debut);
        $end   = Carbon::parse($group->date_fin);

        $availableDates = [];

        while ($start->lte($end)) {
            if (!$start->isWeekend()) {
                $availableDates[] = $start->format('Y-m-d');
            }
            $start->addDay();
        }

        return response()->json($availableDates);
    }
}
