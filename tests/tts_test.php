<?php
require __DIR__ . '/../vendor/autoload.php';
use Google\Cloud\TextToSpeech\V1\TextToSpeechClient;
use Google\Cloud\TextToSpeech\V1\SynthesisInput;
use Google\Cloud\TextToSpeech\V1\VoiceSelectionParams;
use Google\Cloud\TextToSpeech\V1\AudioConfig;
use Google\Cloud\TextToSpeech\V1\AudioEncoding;

$envPath = __DIR__ . '/../.env';
$env = file_exists($envPath) ? parse_ini_file($envPath, false, INI_SCANNER_TYPED) : [];
$credentials = $env['TTS_CREDENTIALS_PATH'] ?? null;
$languageCode = $env['TTS_LANGUAGE_CODE'] ?? 'es-ES';
$voiceName = $env['TTS_VOICE'] ?? null;

if (!$credentials) {
    fwrite(STDERR, "Google credentials are not configured.\n");
    exit(1);
}

$client = new TextToSpeechClient([
    'credentials' => $credentials,
]);

$input = (new SynthesisInput())
    ->setText('Esta es una prueba de TTS.');

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

echo "Created tts_test.mp3\n";

