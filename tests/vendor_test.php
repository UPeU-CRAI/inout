<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use Google\Cloud\TextToSpeech\V1\Client\TextToSpeechClient;

// Cargar el archivo .env
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Obtener la ruta del archivo de credenciales desde el .env
$credentialsPath = $_ENV['TTS_CREDENTIALS_PATH'] ?? '';

if (!file_exists($credentialsPath)) {
    echo "❌ No se encontró el archivo de credenciales en: {$credentialsPath}" . PHP_EOL;
    exit(1);
}

try {
    // Pasar las credenciales directamente al cliente
    $client = new TextToSpeechClient([
        'credentials' => $credentialsPath,
    ]);
    echo "✅ Google Cloud TextToSpeechClient cargado correctamente." . PHP_EOL;
} catch (Throwable $e) {
    echo "❌ Error: " . $e->getMessage() . PHP_EOL;
}
