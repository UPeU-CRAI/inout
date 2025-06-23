<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

echo "<pre>";

try {
    // Cargar .env
    $dotenv = Dotenv::createImmutable(dirname(__DIR__));
    $dotenv->load();

    echo "✅ Archivo .env cargado correctamente.\n";

    // Verificar variables requeridas
    $required = [
        'INOUT_DB_HOST', 'INOUT_DB_USER', 'INOUT_DB_PASS', 'INOUT_DB_NAME',
        'KOHA_DB_HOST', 'KOHA_DB_USER', 'KOHA_DB_PASS', 'KOHA_DB_NAME',
        'TTS_CREDENTIALS_PATH', 'TTS_LANGUAGE_CODE', 'TTS_VOICE'
    ];

    foreach ($required as $var) {
        if (empty($_ENV[$var])) {
            throw new Exception("❌ Variable de entorno faltante: {$var}");
        }
    }

    echo "✅ Todas las variables de entorno requeridas están definidas.\n";

    // Verificar conexión InOut DB
    $inout_conn = new mysqli(
        $_ENV['INOUT_DB_HOST'],
        $_ENV['INOUT_DB_USER'],
        $_ENV['INOUT_DB_PASS'],
        $_ENV['INOUT_DB_NAME']
    );

    if ($inout_conn->connect_error) {
        throw new Exception("❌ Error de conexión InOut DB: " . $inout_conn->connect_error);
    }

    echo "✅ Conexión exitosa a InOut DB.\n";
    $inout_conn->close();

    // Verificar conexión Koha DB
    $koha_conn = new mysqli(
        $_ENV['KOHA_DB_HOST'],
        $_ENV['KOHA_DB_USER'],
        $_ENV['KOHA_DB_PASS'],
        $_ENV['KOHA_DB_NAME']
    );

    if ($koha_conn->connect_error) {
        throw new Exception("❌ Error de conexión Koha DB: " . $koha_conn->connect_error);
    }

    echo "✅ Conexión exitosa a Koha DB.\n";
    $koha_conn->close();

    // Verificar archivo de credenciales TTS
    $ttsPath = $_ENV['TTS_CREDENTIALS_PATH'];
    if (!file_exists($ttsPath)) {
        throw new Exception("❌ Archivo de credenciales TTS no encontrado en: {$ttsPath}");
    }

    echo "✅ Archivo de credenciales TTS encontrado: {$ttsPath}\n";

    echo "\n🎉 Todo está configurado correctamente.\n";

} catch (Throwable $e) {
    echo $e->getMessage();
}

echo "</pre>";
