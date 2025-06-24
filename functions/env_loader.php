<?php

// La declaración 'use' se mueve aquí, al nivel superior del script.
use Dotenv\Dotenv;

if (!defined('ENV_LOADER')) {
    // El resto del script puede continuar como estaba.
    require_once __DIR__ . '/autoload_helper.php';
    require_vendor_autoload(dirname(__DIR__));
    
    $dotenv = Dotenv::createImmutable(dirname(__DIR__));
    $dotenv->load();
    
    $required = [
        'INOUT_DB_HOST', 'INOUT_DB_USER', 'INOUT_DB_PASS', 'INOUT_DB_NAME',
        'KOHA_DB_HOST', 'KOHA_DB_USER', 'KOHA_DB_PASS', 'KOHA_DB_NAME',
        'GOOGLE_APPLICATION_CREDENTIALS'
    ];
    
    foreach ($required as $var) {
        if (!isset($_ENV[$var]) || trim($_ENV[$var]) === '') {
            // Usar die() aquí es más directo si el bootstrap falla.
            die("Error Crítico de Configuración: Falta la variable de entorno requerida: {$var}");
        }
    }
    
    $credPath = $_ENV['GOOGLE_APPLICATION_CREDENTIALS'];
    if (!is_readable($credPath)) {
        die(
            "Error Crítico de Configuración: El archivo de credenciales para Text-to-Speech no se encontró o no se puede leer en '{$credPath}'. Verifica la ruta y los permisos."
        );
    }
    
    define('ENV_LOADER', true);
}
?>
