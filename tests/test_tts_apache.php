<?php
$autoloadPath = __DIR__ . '/vendor/autoload.php';
if (!file_exists($autoloadPath)) {
    throw new RuntimeException(
        "Autoload file not found at '{$autoloadPath}'. Please run 'composer install'."
    );
}
require_once $autoloadPath;

use Google\Cloud\TextToSpeech\V1\TextToSpeechClient;

echo "<pre>";

if (!class_exists(TextToSpeechClient::class)) {
    echo "❌ TextToSpeechClient NO está disponible desde Apache.\n";
} else {
    echo "✅ TextToSpeechClient está disponible desde Apache.\n";
}

