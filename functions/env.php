<?php
/**
 * Simple .env loader used when vlucas/phpdotenv is not installed.
 * Reads key=value pairs from the project root .env file and populates
 * the $_ENV superglobal and environment variables.
 */
if (!defined('ENV_LOADED')) {
    $path = dirname(__DIR__) . '/.env';
    if (is_readable($path)) {
        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            if (strpos(trim($line), '#') === 0) {
                continue;
            }
            if (strpos($line, '=') === false) {
                continue;
            }
            list($name, $value) = array_map('trim', explode('=', $line, 2));
            if ($name !== '' && !array_key_exists($name, $_ENV)) {
                $_ENV[$name] = $value;
                putenv("{$name}={$value}");
            }
        }
    }
    define('ENV_LOADED', true);
}
?>
