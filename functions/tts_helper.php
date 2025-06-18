<?php
// tts_helper.php
// Funciones de ayuda para Text-to-Speech.

// Se asume que el autoload de Composer está manejado globalmente (ej. en dbconn.php)

use Google\Cloud\TextToSpeech\V1\AudioConfig;
use Google\Cloud\TextToSpeech\V1\AudioEncoding;
use Google\Cloud\TextToSpeech\V1\SsmlVoiceGender;
use Google\Cloud\TextToSpeech\V1\SynthesisInput;
use Google\Cloud\TextToSpeech\V1\TextToSpeechClient;
use Google\Cloud\TextToSpeech\V1\VoiceSelectionParams;

/**
 * Genera contenido de audio a partir de texto usando Google Cloud Text-to-Speech.
 *
 * @param string $text El texto a convertir en voz.
 * @param string $credentialsPath Ruta al archivo JSON de credenciales de Google Cloud.
 * @param string $languageCode Código de idioma (ej. 'es-US').
 * @param string|null $voiceName Nombre de la voz (ej. 'es-US-Standard-A'). Opcional.
 * @return string|false El contenido de audio binario en formato MP3, o false en caso de error.
 */
function get_google_tts_audio_content(
    string $text,
    string $credentialsPath,
    string $languageCode = 'es-US',
    ?string $voiceName = null
): string|false {
    try {
        // Verificar si las clases de Google existen; si no, el autoload falló o no está configurado.
        if (!class_exists(TextToSpeechClient::class)) {
            error_log('TTS Error: La clase TextToSpeechClient de Google no se encuentra. Verifica la instalación de Composer y el autoload.php.');
            return false;
        }

        $client = new TextToSpeechClient(['credentials' => $credentialsPath]);

        $input = new SynthesisInput();
        $input->setText($text);

        $voice = new VoiceSelectionParams();
        $voice->setLanguageCode($languageCode);

        if ($voiceName) {
            $voice->setName($voiceName);
        } else {
            $voice->setSsmlGender(SsmlVoiceGender::FEMALE); // Predeterminado a Femenino si no se especifica voz
        }

        $audioConfig = new AudioConfig();
        $audioConfig->setAudioEncoding(AudioEncoding::MP3);
        $audioConfig->setSpeakingRate(1.0); // Velocidad normal, puedes ajustarla

        $response = $client->synthesizeSpeech($input, $voice, $audioConfig);
        $audioContent = $response->getAudioContent();

        $client->close();
        return $audioContent;

    } catch (\Google\ApiCore\ApiException $e) {
        error_log("Google TTS API Error: " . $e->getMessage() . " (Code: " . $e->getCode() . ")");
        if (isset($client) && method_exists($client, 'close')) {
            $client->close();
        }
        return false;
    } catch (\Exception $e) {
        error_log("TTS General Error: " . $e->getMessage());
        // Añadir más detalles si es posible, como el trace del error
        error_log("TTS General Error Trace: " . $e->getTraceAsString());
        if (isset($client) && method_exists($client, 'close')) {
            $client->close();
        }
        return false;
    }
}
?>
