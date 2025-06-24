<?php
/**
 * bootstrap.php
 * Punto de entrada único para la configuración y arranque de TODA la aplicación.
 */

if (defined('APP_BOOTSTRAPPED')) {
    return;
}

// Forzar la visibilidad de errores durante el arranque.
ini_set('display_errors', 1);
error_reporting(E_ALL);

try {
    // 1. Cargar Composer.
    require_once __DIR__ . '/autoload_helper.php';
    require_vendor_autoload(dirname(__DIR__));

    // 2. Cargar variables de entorno (.env).
    $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
    $dotenv->load();
    // (La validación de variables se hará en dbconn.php).

    // 3. Habilitar que MySQLi lance excepciones.
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    
    // 4. --- LA CORRECCIÓN MÁS IMPORTANTE ---
    // Incluir el archivo que define las conexiones Y las funciones.
    require_once __DIR__ . '/dbconn.php';

} catch (Throwable $e) {
    http_response_code(503);
    $debug = $_ENV['DEBUG'] ?? false;
    $message = ($debug && strtolower($debug) !== 'false') ? $e->getMessage() : 'Error en la configuración del servidor.';
    die("<h1>Error Crítico de Aplicación</h1><p>" . htmlspecialchars($message) . "</p>");
}

// 5. Configurar reporte de errores final.
if (!empty($_ENV['DEBUG']) && strtolower($_ENV['DEBUG']) !== 'false') {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

// 6. Iniciar la sesión.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 7. Marcar que el bootstrap se ha completado.
define('APP_BOOTSTRAPPED', true);
