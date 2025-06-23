<?php
require __DIR__ . '/vendor/autoload.php';

use Google\Cloud\TextToSpeech\V1\TextToSpeechClient;

echo "✅ TextToSpeechClient está disponible en Apache.<br>";

$client = new TextToSpeechClient([
    'credentials' => '/u01/vhosts/inout.upeu.edu.pe/credentials/inout-tts.json',
]);

echo "✅ Cliente creado correctamente.";
