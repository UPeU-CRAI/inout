<?php
require __DIR__ . '/vendor/autoload.php';

if (class_exists(\Google\Cloud\TextToSpeech\V1\Client\TextToSpeechClient::class)) {
    echo "✅ Google Cloud TTS está disponible.\n";
} else {
    echo "❌ Google Cloud TTS NO está disponible.\n";
}
