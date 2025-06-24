<?php
if (!defined('ENV_LOADER')) {
    require_once __DIR__ . '/autoload_helper.php';
    require_vendor_autoload(dirname(__DIR__));

    use Dotenv\Dotenv;

    $dotenv = Dotenv::createImmutable(dirname(__DIR__));
    $dotenv->load();

    $required = [
        'INOUT_DB_HOST', 'INOUT_DB_USER', 'INOUT_DB_PASS', 'INOUT_DB_NAME',
        'KOHA_DB_HOST', 'KOHA_DB_USER', 'KOHA_DB_PASS', 'KOHA_DB_NAME',
        'GOOGLE_APPLICATION_CREDENTIALS'
    ];

    foreach ($required as $var) {
        if (!isset($_ENV[$var]) || trim($_ENV[$var]) === '') {
            throw new RuntimeException("❌ Falta la variable de entorno: {$var}");
        }
    }

    $credPath = $_ENV['GOOGLE_APPLICATION_CREDENTIALS'];
    if (!is_readable($credPath)) {
        throw new RuntimeException(
            "El archivo de credenciales para Text-to-Speech no se encontró o no se puede leer en '{$credPath}'."
        );
    }

    define('ENV_LOADER', true);
}
?>
