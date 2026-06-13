<?php

declare(strict_types=1);

namespace App;

/**
 * Loads .env file values into $_ENV / getenv() at application bootstrap.
 *
 * Only simple KEY=VALUE pairs are supported — no multiline values or comments
 * beyond a leading '#' character.  This keeps the implementation dependency-
 * free while being sufficient for shared-hosting deployments.
 */
class Config
{
    public static function load(string $envFile = __DIR__ . '/../.env'): void
    {
        if (!is_file($envFile) || !is_readable($envFile)) {
            return;
        }

        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            $line = trim($line);

            // Skip comments
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            // Only process KEY=VALUE lines
            if (!str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $key   = trim($key);
            $value = trim($value, " \t\n\r\0\x0B\"'");

            if ($key !== '' && !array_key_exists($key, $_ENV)) {
                $_ENV[$key] = $value;
                putenv("{$key}={$value}");
            }
        }
    }
}
