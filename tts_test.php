<?php
require_once __DIR__ . '/vendor/autoload.php';

use Google\Cloud\TextToSpeech\V1\Client\TextToSpeechClient;
use Google\Cloud\TextToSpeech\V1\SynthesizeSpeechRequest;
use Google\Cloud\TextToSpeech\V1\SynthesisInput;
use Google\Cloud\TextToSpeech\V1\VoiceSelectionParams;
use Google\Cloud\TextToSpeech\V1\AudioConfig;
use Google\Cloud\TextToSpeech\V1\AudioEncoding;

putenv('GOOGLE_APPLICATION_CREDENTIALS=' . '/u01/vhosts/inout.upeu.edu.pe/credentials/inout-koha-biblioteca-4b30dc340941.json');

$client = new TextToSpeechClient();

$input = new SynthesisInput([
    'text' => 'Hola, este es un mensaje de prueba.'
]);

$voice = new VoiceSelectionParams([
    'language_code' => 'es-ES',
    'name' => 'es-ES-Standard-A'
]);

$audioConfig = new AudioConfig([
    'audio_encoding' => AudioEncoding::MP3
]);

$request = new SynthesizeSpeechRequest([
    'input' => $input,
    'voice' => $voice,
    'audio_config' => $audioConfig,
]);

$response = $client->synthesizeSpeech($request);
file_put_contents('output.mp3', $response->getAudioContent());

echo "✅ Audio generado correctamente como output.mp3\n";
