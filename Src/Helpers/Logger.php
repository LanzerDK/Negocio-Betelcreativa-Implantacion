<?php

namespace BetelCreativa\Helpers;

// Logger — Sistema de registro de eventos (log) para la aplicación
// Escribe archivos de log rotados por día en la carpeta /logs
class Logger
{
    // Ruta base donde se guardarán los archivos .log
    private static string $logDir = '';

    // Inicializa el directorio de logs; si no existe, lo crea
    public static function init(string $logDir = null): void
    {
        // Si no se pasa ruta, usa la carpeta /logs en la raíz del proyecto
        self::$logDir = $logDir ?? dirname(__DIR__, 2) . '/logs';
        // Crea el directorio si hace falta, con permisos 775
        if (!is_dir(self::$logDir)) {
            @mkdir(self::$logDir, 0775, true);
        }
    }

    // Registra un mensaje de nivel ERROR (fallos graves)
    public static function error(string $message, array $context = []): void
    {
        self::write('ERROR', $message, $context);
    }

    // Registra un mensaje de nivel WARNING (cosas que no deberían pasar pero no rompen)
    public static function warning(string $message, array $context = []): void
    {
        self::write('WARNING', $message, $context);
    }

    // Registra un mensaje de nivel INFO (eventos normales del sistema)
    public static function info(string $message, array $context = []): void
    {
        self::write('INFO', $message, $context);
    }

    // Método interno que escribe la línea formateada al archivo de log del día
    private static function write(string $level, string $message, array $context): void
    {
        // Si nadie llamó init todavía, lo hacemos automáticamente
        if (empty(self::$logDir)) {
            self::init();
        }
        // Timestamp actual para la línea de log
        $date = date('Y-m-d H:i:s');
        // Si hay datos extra (contexto), los convertimos a JSON y los agregamos
        $contextStr = !empty($context) ? ' | ' . json_encode($context, JSON_UNESCAPED_UNICODE) : '';
        // Formato: [2026-07-11 14:30:00] [ERROR] Mensaje aquí | {"key":"val"}
        $line = "[$date] [$level] $message$contextStr" . PHP_EOL;
        // Archivo del día: logs/app-2026-07-11.log
        $file = self::$logDir . '/app-' . date('Y-m-d') . '.log';
        // Escribe al final (FILE_APPEND) con bloqueo para evitar escrituras simultáneas
        @file_put_contents($file, $line, FILE_APPEND | LOCK_EX);
    }
}