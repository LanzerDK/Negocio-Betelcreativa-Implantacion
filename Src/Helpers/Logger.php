<?php

namespace BetelCreativa\Helpers;

class Logger
{
    private static string $logDir = '';

    public static function init(string $logDir = null): void
    {
        self::$logDir = $logDir ?? dirname(__DIR__, 2) . '/logs';
        if (!is_dir(self::$logDir)) {
            @mkdir(self::$logDir, 0775, true);
        }
    }

    public static function error(string $message, array $context = []): void
    {
        self::write('ERROR', $message, $context);
    }

    public static function warning(string $message, array $context = []): void
    {
        self::write('WARNING', $message, $context);
    }

    public static function info(string $message, array $context = []): void
    {
        self::write('INFO', $message, $context);
    }

    private static function write(string $level, string $message, array $context): void
    {
        if (empty(self::$logDir)) {
            self::init();
        }
        $date = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? ' | ' . json_encode($context, JSON_UNESCAPED_UNICODE) : '';
        $line = "[$date] [$level] $message$contextStr" . PHP_EOL;
        $file = self::$logDir . '/app-' . date('Y-m-d') . '.log';
        @file_put_contents($file, $line, FILE_APPEND | LOCK_EX);
    }
}