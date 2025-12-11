<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\GroupApiController;
use App\Models\Site;
use App\Models\Group;

Route::get('/centers', function () {
    return Site::select('id', 'name', 'city')
                ->where('is_active', 1)
                ->get();
});

Route::get('/groups/{site_id}', function ($site_id) {

    $groups = Group::where('site_id', $site_id)
                ->where('status', 'active')
                ->select('id', 'name', 'name_fr', 'level', 'time_range')
                ->get();

    foreach ($groups as $index => $g) {
        if ($g->name_fr) {
            $g->display_name = $g->name_fr;
        } elseif ($g->name) {
            $g->display_name = $g->name;
        } else {
            $g->display_name = "Groupe " . ($index + 1);
        }
    }

    return $groups;
});

Route::get('/groups/dates/{group_id}', [GroupApiController::class, 'getDates']);

Route::get('/groups/time/{group_id}', function ($group_id) {
    $group = Group::where('id', $group_id)
                  ->where('status', 'active')
                  ->select('time_range')
                  ->first();

    return response()->json([
        'time_range' => $group ? $group->time_range : null
    ]);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
