<?php
require_once __DIR__ . '/functions/PersonalizedGreeting.php';

header('Content-Type: text/html; charset=utf-8');

$text = trim($_POST['text'] ?? '');
if ($text !== '') {
    $tts = new PersonalizedGreeting();
    echo $tts->synthesizeVoice($text);
}

