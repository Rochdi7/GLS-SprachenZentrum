<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\GroupApiController;
use App\Models\Site;
use App\Models\Group;


// ============================
// GET ACTIVE CENTERS
// ============================
Route::get('/centers', function () {
    return Site::select('id', 'name', 'city')
                ->where('is_active', 1)
                ->get();
});


// ============================
// GET LEVELS FOR CENTER
// ============================
Route::get('/center/{site_id}/levels', function ($site_id) {
    return Group::where('site_id', $site_id)
                ->select('level')
                ->distinct()
                ->get();
});


// ============================
// GET AVAILABLE DATES (Day by day)
// ============================
Route::get('/groups/dates/{site_id}/{level}', [GroupApiController::class, 'getDates']);


// ============================
// GET TIME RANGE
// ============================
Route::get('/groups/time/{site_id}/{level}', function ($site_id, $level) {
    $group = Group::where('site_id', $site_id)
                  ->where('level', $level)
                  ->where('status', 'active')
                  ->select('time_range')
                  ->first();

    return response()->json([
        'time_range' => $group ? $group->time_range : null
    ]);
});


// ============================
// DEFAULT LARAVEL SANCTUM ROUTE
// ============================
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
