<?php
// play_tts.php - Script para generar y transmitir audio TTS dinámicamente.

// Incluir los archivos necesarios.
// dbconn.php ahora debería incluir el autoload.php de Composer.
require_once __DIR__ . '/functions/dbconn.php'; // Para el autoload y $conn si fuera necesario (aunque no para esta función TTS básica)
require_once __DIR__ . '/functions/tts_helper.php';

// --- Configuración ---
// Ruta al archivo de credenciales JSON de Google Cloud.
// ¡ASEGÚRATE DE QUE ESTA RUTA SEA CORRECTA Y EL ARCHIVO SEA LEGIBLE POR EL SERVIDOR WEB!
$credentialsPath = '/var/www/config_secure/inout-koha-biblioteca-ce4b3cabee16.json';

// Texto por defecto si no se proporciona ninguno (para pruebas rápidas)
$defaultText = 'Hola, la configuración de texto a voz parece funcionar.';
$textToSpeak = isset($_GET['text']) && !empty(trim($_GET['text'])) ? trim($_GET['text']) : $defaultText;

// Parámetros de voz (pueden ser ajustados o pasados por GET también si se desea más flexibilidad)
$languageCode = 'es-US'; // Español de Estados Unidos. Cambiar a 'es-ES' para Español de España si se prefiere.
$voiceName = null;       // Dejar null para que Google elija una voz femenina estándar, o especificar una, ej: 'es-US-Wavenet-A'

// --- Generación y Streaming del Audio ---

// Validar que el archivo de credenciales exista antes de intentar usarlo.
if (!file_exists($credentialsPath)) {
    error_log("TTS Streaming Error: El archivo de credenciales no se encuentra en la ruta: " . $credentialsPath);
    header("HTTP/1.1 500 Internal Server Error");
    echo "Error interno del servidor: configuración de TTS incorrecta.";
    exit;
}

// Obtener el contenido de audio.
$audioContent = get_google_tts_audio_content(
    $textToSpeak,
    $credentialsPath,
    $languageCode,
    $voiceName
);

if ($audioContent === false) {
    // El error ya se habrá logueado dentro de get_google_tts_audio_content()
    header("HTTP/1.1 500 Internal Server Error");
    // No envíes un mensaje de error detallado al cliente por seguridad.
    echo "Error al generar el audio. Inténtalo de nuevo más tarde.";
    exit;
}

// Enviar las cabeceras HTTP correctas para streaming de MP3.
header('Content-Type: audio/mpeg');
header('Content-Transfer-Encoding: binary');
header('Content-Length: ' . strlen($audioContent)); // strlen funciona bien para strings binarios en PHP.

// Cabeceras para intentar prevenir el caché del navegador para audios dinámicos.
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Enviar el contenido del audio al navegador.
echo $audioContent;
exit;

?>
