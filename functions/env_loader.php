<?php
/**
 * Simple .env loader. Reads key=value pairs from the project root
 * and sets them using putenv and \$_ENV if they aren't already set.
 */
function load_env(string $path): void {
    if (!is_file($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        if (!str_contains($line, '=')) {
            continue;
        }
        list($name, $value) = array_map('trim', explode('=', $line, 2));
        if ($name !== '' && getenv($name) === false) {
            // Remove optional quotes around the value and unescape sequences
            if ((str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
                $value = substr($value, 1, -1);
                $value = stripcslashes($value);
            }
            putenv("{$name}={$value}");
            $_ENV[$name] = $value;
        }
    }
}

load_env(__DIR__ . '/../.env');

