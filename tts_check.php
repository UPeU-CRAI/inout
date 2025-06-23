<?php
$autoloadPath = __DIR__ . '/vendor/autoload.php';
if (!file_exists($autoloadPath)) {
    throw new RuntimeException(
        "Autoload file not found at '{$autoloadPath}'. Please run 'composer install'."
    );
}
require $autoloadPath;

if (class_exists(\Google\Cloud\TextToSpeech\V1\Client\TextToSpeechClient::class)) {
    echo "✅ Google Cloud TTS está disponible.\n";
} else {
    echo "❌ Google Cloud TTS NO está disponible.\n";
}
