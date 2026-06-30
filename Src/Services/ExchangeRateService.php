<?php

namespace BetelCreativa\Services;

use BetelCreativa\Infrastructure\SettingsRepository;

class ExchangeRateService
{
    private SettingsRepository $settings;

    public function __construct()
    {
        $this->settings = new SettingsRepository();
    }

    public function getEffectiveRate(): float
    {
        $newRate = $this->fetchFromApi();
        if ($newRate > 0) {
            $this->settings->set('bcv_rate', (string)$newRate);
            return $newRate;
        }
        $saved = $this->settings->get('bcv_rate', '36.50');
        return (float)$saved;
    }

    private function fetchFromApi(): float
    {
        try {
            $ctx = stream_context_create(['http' => ['timeout' => 3]]);
            $response = @file_get_contents('https://ve.dolarapi.com/v1/dolares/oficial', false, $ctx);
            if ($response === false) return 0.0;
            $data = json_decode($response, true);
            if (!is_array($data)) return 0.0;
            return (float)($data['promedio'] ?? $data['rate'] ?? 0);
        } catch (\Exception $e) {
            return 0.0;
        }
    }
}
