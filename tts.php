<?php
require_once __DIR__ . '/functions/dbconn.php';
require_once __DIR__ . '/functions/dbfunc.php';
require_once __DIR__ . '/functions/PersonalizedGreeting.php';

header('Content-Type: text/html; charset=utf-8');

$text = trim($_POST['text'] ?? '');
if ($text !== '') {
    $provider = get_setting($conn, 'tts_provider') ?: 'google';
    $tts = new PersonalizedGreeting($provider);
    echo $tts->synthesizeVoice($text);
}

