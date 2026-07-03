<?php

namespace App\Http\Controllers\Backoffice\Hikvision;

use App\Http\Controllers\Controller;
use App\Services\Hikvision\HikvisionOverviewService;

class SettingsController extends Controller
{
    public function __construct(private readonly HikvisionOverviewService $overviewService) {}

    public function index()
    {
        return view('backoffice.hikvision.settings.index', $this->overviewService->settingsPayload());
    }
}
