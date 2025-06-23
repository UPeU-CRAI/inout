<?php
$autoloadPath = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($autoloadPath)) {
    throw new RuntimeException(
        "Autoload file not found at '{$autoloadPath}'. Please run 'composer install'."
    );
}
require_once $autoloadPath;

use Google\Cloud\TextToSpeech\V1\TextToSpeechClient;
use Google\Cloud\TextToSpeech\V1\SynthesisInput;
use Google\Cloud\TextToSpeech\V1\VoiceSelectionParams;
use Google\Cloud\TextToSpeech\V1\AudioConfig;
use Google\Cloud\TextToSpeech\V1\AudioEncoding;


/**
 * PersonalizedGreeting: Clase para construir y sintetizar saludos personalizados
 * utilizando la API de Google Cloud Text-to-Speech.
 */
class PersonalizedGreeting
{
    private $languageCode;
    private $credentialsPath;
    private $voiceName;

    /**
     * Constructor: carga configuración desde .env y valida credenciales.
     */
    public function __construct()
    {
        $envPath = dirname(__DIR__) . '/.env';
        $env = file_exists($envPath) ? parse_ini_file($envPath, false, INI_SCANNER_TYPED) : [];

        $this->credentialsPath = $env['TTS_CREDENTIALS_PATH'] ?? '';
        $this->languageCode = $env['TTS_LANGUAGE_CODE'] ?? 'es-ES';
        $this->voiceName = $env['TTS_VOICE'] ?? null;

        if (empty($this->credentialsPath) || !is_readable($this->credentialsPath)) {
            throw new RuntimeException(
                "Text-to-Speech credentials file not found or unreadable at '{$this->credentialsPath}'. "
                . 'Check the TTS_CREDENTIALS_PATH variable.'
            );
        }
    }

    /**
     * Construye un saludo personalizado en texto.
     */
    public function buildGreeting(string $name, string $timeOfDay, string $category, ?string $birthday = null, ?string $country = null): string
    {
        $timeOfDay = strtolower($timeOfDay);
        $greeting = 'Hola';

        if ($timeOfDay === 'morning') {
            $greeting = 'Buenos días';
        } elseif ($timeOfDay === 'afternoon') {
            $greeting = 'Buenas tardes';
        } elseif ($timeOfDay === 'evening' || $timeOfDay === 'night') {
            $greeting = 'Buenas noches';
        }

        $greeting .= ' ' . $name;

        if ($category === 'DOCEN') {
            $greeting .= ', docente';
        } elseif ($category === 'VISITA') {
            $greeting .= ', visitante';
        }

        if ($country) {
            $greeting .= ' de ' . $country;
        }

        if ($birthday && date('m-d') === date('m-d', strtotime($birthday))) {
            $greeting .= '. ¡Feliz cumpleaños!';
        }

        return $greeting . '.';
    }

    /**
     * Convierte el texto en audio (MP3) usando Google Cloud TTS.
     * Devuelve una etiqueta <audio> lista para reproducirse automáticamente.
     */
    public function synthesizeGreeting(string $text): string
    {
        if (!class_exists(TextToSpeechClient::class)) {
            throw new RuntimeException('Google Cloud Text-to-Speech library not found.');
        }

        try {
            $client = new TextToSpeechClient([
                'credentials' => $this->credentialsPath,
            ]);

            $input = (new SynthesisInput())->setText($text);

            $voice = (new VoiceSelectionParams())->setLanguageCode($this->languageCode);
            if ($this->voiceName) {
                $voice->setName($this->voiceName);
            }

            $audioConfig = (new AudioConfig())->setAudioEncoding(AudioEncoding::MP3);

            $response = $client->synthesizeSpeech([
                'input' => $input,
                'voice' => $voice,
                'audioConfig' => $audioConfig,
            ]);

            $client->close();

            $audioContent = $response->getAudioContent();
            $base64Audio = base64_encode($audioContent);

            return '<audio controls autoplay src="data:audio/mp3;base64,' . $base64Audio . '"></audio>';

        } catch (\Throwable $e) {
            return '<p style="color: red;">❌ Error en síntesis: ' . htmlspecialchars($e->getMessage()) . '</p>';
        }
    }
}
