<?php

namespace BetelCreativa\Config;

/**
 * Clase EnvLoader — Carga y accede a variables de entorno desde el archivo .env.
 * Parsea pares clave=valor, elimina comillas, y almacena en caché interna,
 * $_ENV y putenv() para máximo compatibilidad.
 */
class EnvLoader
{
    /* Caché interna de pares clave→valor ya parseados */
    private static array $loaded = [];

    /**
     * Lee y parsea el archivo .env indicado.
     * Omite líneas vacías y comentarios (#).
     * Si el archivo no existe, sale sin error.
     */
    public static function load(string $path = null): void
    {
        self::$loaded = [];
        /* Por defecto busca .env en la raíz del proyecto */
        $path = $path ?? __DIR__ . '/../.env';

        if (!file_exists($path)) {
            return;
        }

        /* Lee todas las líneas sin saltos de línea ni líneas vacías */
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            $line = trim($line);
            /* Salta líneas vacías y comentarios */
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            /* Extrae clave y valor separados por el primer '=' */
            if (str_contains($line, '=')) {
                [$key, $value] = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                /* Elimina comillas simples o dobles del valor */
                $value = trim($value, '"\'');

                /* Almacena en las 3 capas: caché, $_ENV y putenv */
                self::$loaded[$key] = $value;
                $_ENV[$key] = $value;
                putenv("$key=$value");
            }
        }
    }

    /**
     * Obtiene el valor de una variable de entorno por su clave.
     * Busca en orden: caché → $_ENV → getenv() → valor por defecto.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return self::$loaded[$key]
            ?? $_ENV[$key]
            ?? (($val = getenv($key)) !== false ? $val : null)
            ?? $default;
    }
}
