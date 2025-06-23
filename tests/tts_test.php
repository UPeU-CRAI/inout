<?php
require __DIR__ . '/../vendor/autoload.php';

// Cargar variables del .env manualmente
$envPath = dirname(__DIR__) . '/.env';
$env = file_exists($envPath) ? parse_ini_file($envPath, false, INI_SCANNER_TYPED) : [];

// Obtener credenciales de entorno
$credentials = $env['TTS_CREDENTIALS_PATH'] ?? null;
$languageCode = $env['TTS_LANGUAGE_CODE'] ?? 'es-ES';
$voiceName = $env['TTS_VOICE'] ?? null;

if (!$credentials) {
    fwrite(STDERR, "❌ ERROR: TTS_CREDENTIALS_PATH no está configurado.\n");
    exit(1);
}

putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $credentials);

use Google\Cloud\TextToSpeech\V1\Client\TextToSpeechClient;
use Google\Cloud\TextToSpeech\V1\SynthesisInput;
use Google\Cloud\TextToSpeech\V1\VoiceSelectionParams;
use Google\Cloud\TextToSpeech\V1\AudioConfig;
use Google\Cloud\TextToSpeech\V1\AudioEncoding;
use Google\Cloud\TextToSpeech\V1\SynthesizeSpeechRequest;

// Crear cliente
$client = new TextToSpeechClient([
    'credentials' => $credentials,
]);

// Definir texto de prueba
$input = (new SynthesisInput())
    ->setText('Esta es una prueba de TTS.');

// Definir voz
$voice = (new VoiceSelectionParams())
    ->setLanguageCode($languageCode);

if ($voiceName) {
    $voice->setName($voiceName);
}

// Configuración de audio

$audioConfig = (new AudioConfig())
    ->setAudioEncoding(AudioEncoding::MP3);
$request = (new SynthesizeSpeechRequest())
    ->setInput($input)
    ->setVoice($voice)
    ->setAudioConfig($audioConfig);

// Ejecutar la síntesis
$response = $client->synthesizeSpeech($request);
$client->close();

// Guardar resultado
$outputFile = __DIR__ . '/tts_test.mp3';
file_put_contents($outputFile, $response->getAudioContent());

echo "✅ Audio guardado en: $outputFile\n";
