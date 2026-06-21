<?php

namespace BetelCreativa\Config;

class EnvLoader
{
    private static array $loaded = [];

    public static function load(string $path = null): void
    {
        self::$loaded = [];
        $path = $path ?? __DIR__ . '/../.env';
        if (!file_exists($path)) {
            return;
        }
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            if (str_contains($line, '=')) {
                [$key, $value] = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                $value = trim($value, '"\'');
                self::$loaded[$key] = $value;
                $_ENV[$key] = $value;
                putenv("$key=$value");
            }
        }
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return self::$loaded[$key]
            ?? $_ENV[$key]
            ?? getenv($key)
            ?: $default;
    }
}
