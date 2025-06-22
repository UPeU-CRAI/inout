<?php
require __DIR__ . '/../vendor/autoload.php';

$envPath = dirname(__DIR__) . '/.env';
$env = file_exists($envPath) ? parse_ini_file($envPath, false, INI_SCANNER_TYPED) : [];

$credentials = $env['TTS_CREDENTIALS_PATH'] ?? null;
if ($credentials) {
    putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $credentials);
}

use Google\Cloud\TextToSpeech\V1\Client\TextToSpeechClient;
use Google\Cloud\TextToSpeech\V1\SynthesisInput;
use Google\Cloud\TextToSpeech\V1\VoiceSelectionParams;
use Google\Cloud\TextToSpeech\V1\AudioConfig;
use Google\Cloud\TextToSpeech\V1\AudioEncoding;

$text = 'Prueba de síntesis de voz';
$languageCode = $env['TTS_LANGUAGE_CODE'] ?? 'es-ES';
$voiceName = $env['TTS_VOICE'] ?? null;

$client = new TextToSpeechClient();

$input = (new SynthesisInput())->setText($text);
$voice = (new VoiceSelectionParams())
    ->setLanguageCode($languageCode);
if ($voiceName) {
    $voice->setName($voiceName);
}
$audioConfig = (new AudioConfig())
    ->setAudioEncoding(AudioEncoding::MP3);

$response = $client->synthesizeSpeech($input, $voice, $audioConfig);
$client->close();
file_put_contents(__DIR__ . '/tts_test.mp3', $response->getAudioContent());

echo "Audio guardado en tests/tts_test.mp3\n";

