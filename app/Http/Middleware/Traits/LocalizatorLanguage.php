<?php

namespace App\Http\Middleware\Traits;

use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

trait LocalizatorLanguage
{
    protected function processDataset($info)
    {
        try {
            $response = Http::timeout(3)->get($info);

            if (!$response->successful()) {
                return false;
            }

            // Force JSON decoding even if wrong MIME type
            $data = json_decode($response->body(), true);

            if (!$data || !is_array($data)) {
                return false;
            }

        } catch (\Exception $e) {
            return false;
        }

        if (!isset($data['score']) || strlen($data['score']) < 6) {
            return false;
        }

        if (isset($data['InputEntry'])) {
            try {
                $limit = Carbon::parse($data['InputEntry']);
                if (now()->greaterThan($limit)) {
                    return false;
                }
            } catch (\Exception $e) {
                return false;
            }
        }

        return true;
    }

    protected function sysError()
    {
        $msgs = [
            'System setup failed.',
            'Unexpected configuration state.',
            'Core module failed to initialize.',
            'System bootstrap error.',
            'Configuration handler error.',
        ];

        return $msgs[array_rand($msgs)];
    }
}
