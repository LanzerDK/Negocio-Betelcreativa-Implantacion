<?php

namespace BetelCreativa\Services;

use BetelCreativa\Infrastructure\SettingsRepository;

// ExchangeRateService — Obtención de la tasa de cambio BCV (Bolívar ↔ Dólar)
// Intenta obtener la tasa desde la API de DolarAPI, con fallback al último valor guardado en settings
class ExchangeRateService
{
    private SettingsRepository $settings;

    public function __construct()
    {
        $this->settings = new SettingsRepository();
    }

    // Devuelve la tasa efectiva: primero intenta API en vivo, si falla usa el valor persistido en BD
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

    // Consulta la tasa oficial desde DolarAPI con timeout de 3 segundos
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
