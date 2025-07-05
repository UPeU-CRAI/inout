<?php
require_once __DIR__ . '/functions/env_loader.php';

$provider = strtolower(getenv('TTS_PROVIDER') ?: 'google');
if ($provider === 'azure') {
    require_once __DIR__ . '/functions/AzureSpeech.php';
    $tts = new AzureSpeech();
} else {
    require_once __DIR__ . '/functions/PersonalizedGreeting.php';
    $tts = new PersonalizedGreeting();
}

header('Content-Type: text/html; charset=utf-8');

$text = trim($_POST['text'] ?? '');
if ($text !== '') {
    echo $tts->synthesizeVoice($text);
}

