<?php
require_once __DIR__ . '/functions/dbconn.php';
require_once __DIR__ . '/functions/dbfunc.php';
require_once __DIR__ . '/functions/PersonalizedGreeting.php';

header('Content-Type: application/json; charset=utf-8');

// Obtiene el texto enviado por el formulario.
$text = trim($_POST['text'] ?? '');

$male   = '';
$female = '';

// Procesa solo si hay texto.
if ($text !== '') {
    // 1. Determina qué proveedor de TTS usar (google o azure) desde la configuración.
    $provider = get_setting($conn, 'tts_provider') ?: 'google';

    // 2. Crea una instancia del servicio de saludo, pasándole el proveedor elegido.
    $tts = new PersonalizedGreeting($provider);

    // 3. Sintetiza el audio para ambas voces usando el proveedor seleccionado.
    // Llama a synthesizeVoiceData para obtener el audio en base64.
    $male   = $tts->synthesizeVoiceData($text, 'M') ?? '';
    $female = $tts->synthesizeVoiceData($text, 'F') ?? '';
}

// 4. Devuelve el resultado en formato JSON, como esperaba el script original.
echo json_encode([
    'audio_male'   => $male,
    'audio_female' => $female,
]);