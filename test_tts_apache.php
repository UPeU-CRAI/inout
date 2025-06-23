<?php
require_once __DIR__ . '/vendor/autoload.php';

use Google\Cloud\TextToSpeech\V1\TextToSpeechClient;

echo "<pre>";

if (!class_exists(TextToSpeechClient::class)) {
    echo "❌ TextToSpeechClient NO está disponible desde Apache.\n";
} else {
    echo "✅ TextToSpeechClient está disponible desde Apache.\n";
}

