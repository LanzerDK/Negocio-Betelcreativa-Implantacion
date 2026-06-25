<?php

namespace BetelCreativa\Helpers;

/**
 * SmsService — Envío de SMS vía TextBelt API
 * ------------------------------------------------------------
 * TextBelt (https://textbelt.com) es una API de SMS simple.
 * 
 * Uso gratuito: usar key='textbelt' (1 SMS/día a nivel mundial).
 * Uso pago:    comprar una API key en https://textbelt.com
 * 
 * Soporta 221 países. Para Venezuela usar formato E.164: +58412XXXXXXX
 */
class SmsService
{
    /**
     * Envía un código de verificación por SMS
     *
     * @param string $phone  Número en formato local (ej: 0414-555-12-34)
     * @param string $code   Código de 6 dígitos
     * @return array ['success' => bool, 'message' => string]
     */
    public static function sendCode(string $phone, string $code): array
    {
        // ── 1. Normalizar número a formato E.164 ──────────────────────
        //    Entrada: 0414-555-12-34
        //    Salida:  +584145551234
        $cleaned = preg_replace('/[^0-9]/', '', $phone);

        // Si el número ya empieza con +58 o 58, no duplicar código de país
        if (str_starts_with($cleaned, '58')) {
            $e164 = '+' . $cleaned;
        } elseif (str_starts_with($cleaned, '0')) {
            // Número venezolano: 0414XXXXXXX → +58414XXXXXXX
            $e164 = '+58' . substr($cleaned, 1);
        } else {
            // Asumir que ya es internacional o local sin prefijo
            $e164 = '+58' . $cleaned;
        }

        // ── 2. Preparar mensaje ──────────────────────────────────────
        $appName = defined('APP_NAME') ? APP_NAME : 'Betel Creativa';
        $message = "{$appName}: tu código de verificación es {$code}. Válido por 10 minutos.";

        // ── 3. Llamar a la API de TextBelt ───────────────────────────
        $apiKey = defined('TEXTBELT_KEY') ? TEXTBELT_KEY : 'textbelt';

        $postData = http_build_query([
            'phone'   => $e164,
            'message' => $message,
            'key'     => $apiKey,
            'sender'  => $appName,
        ]);

        $ch = curl_init('https://textbelt.com/text');
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $postData,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        // ── 4. Interpretar respuesta ─────────────────────────────────
        if ($curlError) {
            Logger::warning("SMS falló (cURL): {$curlError}");
            return [
                'success' => false,
                'message' => "Error de conexión con el servicio SMS: {$curlError}",
            ];
        }

        $data = json_decode($response, true);

        if ($httpCode !== 200 || !$data || !isset($data['success'])) {
            Logger::warning("SMS falló (HTTP {$httpCode}): " . ($data['message'] ?? 'respuesta inválida'));
            return [
                'success' => false,
                'message' => 'El servicio SMS respondió con un error.',
            ];
        }

        if ($data['success']) {
            $textId = $data['textId'] ?? 'N/A';
            Logger::info("SMS enviado a {$e164} (textId: {$textId})");
            return [
                'success' => true,
                'message' => 'Código enviado por SMS.',
            ];
        }

        // TextBelt devolvió success=false (cuota excedida, número inválido, etc.)
        Logger::warning("SMS no enviado a {$e164}: {$data['message']}");

        // Si la key es 'textbelt' y dice "Exceeded quota", informar al usuario
        if ($apiKey === 'textbelt' && str_contains($data['message'] ?? '', 'quota')) {
            return [
                'success' => false,
                'message' => 'Límite diario de SMS alcanzado. Intenta de nuevo mañana o usa el correo electrónico.',
            ];
        }

        return [
            'success' => false,
            'message' => $data['message'] ?? 'Error al enviar SMS.',
        ];
    }
}
