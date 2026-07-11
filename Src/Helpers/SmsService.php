<?php

namespace BetelCreativa\Helpers;

// SmsService — Envío de SMS vía TextBelt API
// TextBelt (https://textbelt.com) es un servicio simple de mensajes de texto.
// Modo gratuito: key='textbelt' permite 1 SMS/día a nivel mundial.
// Números venezolanos se envían en formato E.164: +58412XXXXXXX
class SmsService
{
    // Envía un código de verificación por SMS al número indicado
    // Recibe el número en formato local (ej: 0414-555-12-34) y el código de 6 dígitos
    public static function sendCode(string $phone, string $code): array
    {
        // ── 1. Normalizar número a formato E.164 ──────────────────────
        //    De "0414-555-12-34" a "+584145551234"
        //    Primero eliminamos todo lo que no sea dígito
        $cleaned = preg_replace('/[^0-9]/', '', $phone);

        // Si ya empieza con 58, solo agregamos el +
        if (str_starts_with($cleaned, '58')) {
            $e164 = '+' . $cleaned;
        } elseif (str_starts_with($cleaned, '0')) {
            // Número venezolano típico: 0414XXXXXXX → +58414XXXXXXX (quitamos el 0 inicial y anteponemos +58)
            $e164 = '+58' . substr($cleaned, 1);
        } else {
            // Si no reconocemos el formato, asumimos número local sin prefijo y le agregamos +58
            $e164 = '+58' . $cleaned;
        }

        // ── 2. Preparar el mensaje de texto ──────────────────────────
        $appName = defined('APP_NAME') ? APP_NAME : 'Betel Creativa';
        $message = "{$appName}: tu código de verificación es {$code}. Válido por 10 minutos.";

        // ── 3. Llamar a la API de TextBelt ───────────────────────────
        // Si no hay key configurada en .env, usa 'textbelt' (plan gratuito, 1 SMS/día)
        $apiKey = defined('TEXTBELT_KEY') ? TEXTBELT_KEY : 'textbelt';

        // Prepara los datos del formulario para enviar vía POST a TextBelt
        $postData = http_build_query([
            'phone'   => $e164,
            'message' => $message,
            'key'     => $apiKey,
            'sender'  => $appName,
        ]);

        // Inicia cURL hacia la API de TextBelt
        $ch = curl_init('https://textbelt.com/text');
        // Configura las opciones: POST, timeout de 15 segundos, devuelve el resultado como string
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $postData,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
        ]);

        // Ejecuta la llamada y captura respuesta, código HTTP y error de cURL
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        // ── 4. Interpretar la respuesta de TextBelt ──────────────────
        // Si hubo un error de conexión (no de la API), lo reportamos
        if ($curlError) {
            Logger::warning("SMS falló (cURL): {$curlError}");
            return [
                'success' => false,
                'message' => "Error de conexión con el servicio SMS: {$curlError}",
            ];
        }

        // Decodifica la respuesta JSON de TextBelt
        $data = json_decode($response, true);

        // Si el HTTP code no es 200 o la respuesta no tiene el campo 'success', algo salió mal
        if ($httpCode !== 200 || !$data || !isset($data['success'])) {
            Logger::warning("SMS falló (HTTP {$httpCode}): " . ($data['message'] ?? 'respuesta inválida'));
            return [
                'success' => false,
                'message' => 'El servicio SMS respondió con un error.',
            ];
        }

        // Si TextBelt confirmó el envío, reportamos éxito
        if ($data['success']) {
            $textId = $data['textId'] ?? 'N/A';
            Logger::info("SMS enviado a {$e164} (textId: {$textId})");
            return [
                'success' => true,
                'message' => 'Código enviado por SMS.',
            ];
        }

        // Si llegamos aquí, TextBelt devolvió success=false (cuota excedida, número inválido, etc.)
        Logger::warning("SMS no enviado a {$e164}: {$data['message']}");

        // Si usa la key gratuita y excedió la cuota, damos un mensaje amigable
        if ($apiKey === 'textbelt' && str_contains($data['message'] ?? '', 'quota')) {
            return [
                'success' => false,
                'message' => 'Límite diario de SMS alcanzado. Intenta de nuevo mañana o usa el correo electrónico.',
            ];
        }

        // Cualquier otro error de TextBelt
        return [
            'success' => false,
            'message' => $data['message'] ?? 'Error al enviar SMS.',
        ];
    }
}
