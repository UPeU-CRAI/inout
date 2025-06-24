<?php

declare(strict_types=1);

// Se asegura de que el cargador de Composer esté disponible.
// La ruta es relativa al directorio del script que instancia esta clase.
require_once dirname(__DIR__) . '/vendor/autoload.php';

use Google\Cloud\TextToSpeech\V1\AudioConfig;
use Google\Cloud\TextToSpeech\V1\AudioEncoding;
use Google\Cloud\TextToSpeech\V1\SynthesisInput;
use Google\Cloud\TextToSpeech\V1\TextToSpeechClient;
use Google\Cloud\TextToSpeech\V1\VoiceSelectionParams;
use Dotenv\Dotenv;
use RuntimeException;
use Throwable;

/**
 * Gestiona la creación y síntesis de saludos de voz personalizados
 * utilizando la API Text-to-Speech de Google Cloud.
 */
class PersonalizedGreeting
{
    // Constantes para categorías de usuario, evita el uso de "magic strings".
    private const CATEGORY_TEACHER = 'DOCEN';
    private const CATEGORY_VISITOR = 'VISITA';

    // Constantes para momentos del día
    private const TIME_MORNING = 'morning';
    private const TIME_AFTERNOON = 'afternoon';
    private const TIME_EVENING = 'evening';
    private const TIME_NIGHT = 'night';

    public readonly string $languageCode;
    public readonly string $credentialsPath;
    public readonly ?string $voiceName;

    /**
     * Carga la configuración del entorno y valida las credenciales de Google Cloud.
     *
     * @throws RuntimeException Si el archivo de credenciales no se encuentra o no se puede leer.
     */
    public function __construct()
    {
        // Carga las variables de entorno desde el directorio raíz del proyecto.
        $dotenv = Dotenv::createImmutable(dirname(__DIR__));
        $dotenv->load();

        $this->credentialsPath = $_ENV['GOOGLE_APPLICATION_CREDENTIALS'] ?? '';
        $this->languageCode = $_ENV['TTS_LANGUAGE_CODE'] ?? 'es-ES';
        $this->voiceName = $_ENV['TTS_VOICE'] ?? null;

        if (empty($this->credentialsPath) || !is_readable($this->credentialsPath)) {
            throw new RuntimeException(
                "El archivo de credenciales para Text-to-Speech no se encontró o no se puede leer en '{$this->credentialsPath}'. " .
                'Verifica la variable GOOGLE_APPLICATION_CREDENTIALS en tu archivo .env.'
            );
        }
    }

    /**
     * Construye un texto de saludo personalizado.
     *
     * @param string      $name       El nombre de la persona.
     * @param string      $timeOfDay  El momento del día (morning, afternoon, evening, night).
     * @param string      $category   La categoría del usuario (e.g., 'DOCEN', 'VISITA').
     * @param string|null $birthday   La fecha de cumpleaños (opcional).
     * @param string|null $country    El país de origen (opcional).
     * @return string El texto del saludo completo.
     */
    public function buildGreeting(string $name, string $timeOfDay, string $category, ?string $birthday = null, ?string $country = null): string
    {
        $greetingPrefix = match (strtolower($timeOfDay)) {
            self::TIME_MORNING => 'Buenos días',
            self::TIME_AFTERNOON => 'Buenas tardes',
            self::TIME_EVENING, self::TIME_NIGHT => 'Buenas noches',
            default => 'Hola',
        };

        $parts = [$greetingPrefix, $name];

        if ($category === self::CATEGORY_TEACHER) {
            $parts[] = ', docente';
        } elseif ($category === self::CATEGORY_VISITOR) {
            $parts[] = ', visitante';
        }

        if ($country) {
            $parts[] = 'de ' . $country;
        }

        // Comprueba si hoy es el cumpleaños del usuario.
        if ($birthday && date('m-d') === date('m-d', strtotime($birthday))) {
            $parts[] = '. ¡Feliz cumpleaños!';
        }

        return implode(' ', $parts) . '.';
    }

    /**
     * Sintetiza un texto a voz y devuelve una etiqueta de audio HTML5.
     *
     * @param string $text El texto a sintetizar.
     * @return string Una etiqueta <audio> con el contenido en base64, o un mensaje de error.
     */
    public function synthesizeGreeting(string $text): string
    {
        try {
            // Se recomienda un solo cliente por solicitud para gestionar la conexión.
            $client = new TextToSpeechClient(['credentials' => $this->credentialsPath]);

            $input = (new SynthesisInput())->setText($text);
            
            $voice = (new VoiceSelectionParams())->setLanguageCode($this->languageCode);
            if ($this->voiceName) {
                $voice->setName($this->voiceName);
            }

            $audioConfig = (new AudioConfig())->setAudioEncoding(AudioEncoding::MP3);

            $response = $client->synthesizeSpeech(compact('input', 'voice', 'audioConfig'));
            $client->close(); // Cierra la conexión gRPC.

            $audioContent = $response->getAudioContent();
            $base64Audio = base64_encode($audioContent);

            // **CORRECCIÓN CLAVE**: Devolver la etiqueta <audio> con el contenido.
            // El atributo 'autoplay' puede ser bloqueado por los navegadores modernos,
            // pero 'controls' permite al usuario iniciar la reproducción manualmente.
            return sprintf(
                '<audio controls autoplay src="data:audio/mp3;base64,%s">Tu navegador no soporta audio HTML5.</audio>',
                $base64Audio
            );
        } catch (Throwable $e) {
            // Registra el error real en los logs del servidor para depuración.
            error_log('Error en síntesis de voz de Google: ' . $e->getMessage());
            
            // Devuelve un mensaje de error amigable al usuario.
            return '❌ Error en síntesis de voz: ' . htmlspecialchars($e->getMessage());
        }
    }
}
