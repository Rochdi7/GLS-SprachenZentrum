<?php

namespace App\Http\Controllers\Frontoffice;

use App\Http\Controllers\Controller;
use App\Models\Translation;
use Illuminate\Http\Request;

class TranslationTrackingController extends Controller
{
    public function show(Request $request)
    {
        $cin    = Translation::normalizeCin($request->query('cin', ''));
        $orders = collect();
        $searched = false;

        if ($cin !== '') {
            $searched = true;
            $orders = Translation::where('cin', $cin)
                ->orderByDesc('date_received')
                ->orderByDesc('id')
                ->get();
        }

        return view('frontoffice.translations.track', [
            'cin'      => $cin,
            'orders'   => $orders,
            'searched' => $searched,
        ]);
    }
}
