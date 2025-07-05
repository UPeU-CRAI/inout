<?php
require_once __DIR__ . '/functions/PersonalizedGreeting.php';

header('Content-Type: application/json; charset=utf-8');

$text = trim($_POST['text'] ?? '');
$tts = new PersonalizedGreeting();

$male  = '';
$female = '';

if ($text !== '') {
    $male   = $tts->synthesizeVoiceData($text, 'M') ?? '';
    $female = $tts->synthesizeVoiceData($text, 'F') ?? '';
}

echo json_encode([
    'audio_male'   => $male,
    'audio_female' => $female,
]);


