<?php

namespace BetelCreativa\Helpers;

use Resend;

// EmailService — Envío de correos electrónicos vía Resend API
// Resend (https://resend.com) es un servicio moderno de email para desarrolladores.
// Plan gratuito: 100 emails/día. Requiere verificar un dominio propio en Resend.
// Para pruebas se puede usar onboarding@resend.dev como remitente.
class EmailService
{
    // Envía un código de verificación de 6 dígitos por correo electrónico
    // Devuelve un array con 'success' (bool) y 'message' (string)
    public static function sendCode(string $email, string $code): array
    {
        // ── 1. Validar que la API key de Resend esté configurada en .env ──
        $apiKey = defined('RESEND_API_KEY') ? RESEND_API_KEY : '';
        if (empty($apiKey)) {
            Logger::warning("Email no enviado a {$email}: RESEND_API_KEY no configurada");
            return [
                'success' => false,
                'message' => 'El servicio de correo no está configurado. Contacta al administrador.',
            ];
        }

        // ── 2. Configurar el remitente del correo ─────────────────────────
        $appName = defined('APP_NAME') ? APP_NAME : 'Betel Creativa';
        // Si no hay RESEND_FROM_EMAIL configurado, usa la dirección por defecto de Resend para pruebas
        $fromEmail = defined('RESEND_FROM_EMAIL') ? RESEND_FROM_EMAIL : 'onboarding@resend.dev';
        $from = "{$appName} <{$fromEmail}>";

        // ── 3. Construir el HTML del correo con estilo visual ─────────────
        $html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 480px; margin: 40px auto; background: #fff; border-radius: 12px; padding: 32px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); }
        .logo { text-align: center; font-size: 24px; font-weight: 700; color: #0A369D; margin-bottom: 8px; }
        .code { text-align: center; font-size: 36px; font-weight: 700; letter-spacing: 8px; color: #0A369D; background: #f0f4ff; border-radius: 8px; padding: 16px; margin: 24px 0; }
        .info { color: #666; font-size: 14px; text-align: center; margin-bottom: 8px; }
        .footer { text-align: center; color: #999; font-size: 12px; margin-top: 24px; border-top: 1px solid #eee; padding-top: 16px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">{$appName}</div>
        <p style="text-align:center;color:#444;">Tu código de verificación</p>
        <div class="code">{$code}</div>
        <p class="info">Este código expira en 10 minutos.</p>
        <p class="info">Si no solicitaste este cambio, ignora este mensaje.</p>
        <div class="footer">&copy; "{$appName}" — Todos los derechos reservados.</div>
    </div>
</body>
</html>
HTML;

        // ── 4. Enviar el correo a través del SDK de Resend ───────────────
        try {
            // Crea el cliente de Resend con la API key
            $resend = Resend::client($apiKey);

            // Envía el correo: remitente, destinatario, asunto y cuerpo HTML
            $result = $resend->emails->send([
                'from'    => $from,
                'to'      => [$email],
                'subject' => "{$appName} — Código de verificación",
                'html'    => $html,
            ]);

            Logger::info("Email enviado a {$email} (id: {$result->id})");

            return [
                'success' => true,
                'message' => 'Código enviado por correo electrónico.',
            ];

        } catch (\Throwable $e) {
            // Captura cualquier error (key inválida, dominio no verificado, etc.)
            Logger::warning("Email falló para {$email}: {$e->getMessage()}");
            return [
                'success' => false,
                'message' => 'Error al enviar el correo electrónico: ' . $e->getMessage(),
            ];
        }
    }
}
