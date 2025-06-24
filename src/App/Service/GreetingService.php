<?php
namespace App\Service;

use Google\Cloud\TextToSpeech\V1\AudioConfig;
use Google\Cloud\TextToSpeech\V1\AudioEncoding;
use Google\Cloud\TextToSpeech\V1\SynthesisInput;
use Google\Cloud\TextToSpeech\V1\TextToSpeechClient;
use Google\Cloud\TextToSpeech\V1\VoiceSelectionParams;
use RuntimeException;
use Throwable;

class GreetingService
{
    private const CATEGORY_TEACHER = 'DOCEN';
    private const CATEGORY_VISITOR = 'VISITA';

    private const TIME_MORNING = 'morning';
    private const TIME_AFTERNOON = 'afternoon';
    private const TIME_EVENING = 'evening';
    private const TIME_NIGHT = 'night';

    public readonly string $languageCode;
    public readonly string $credentialsPath;
    public readonly ?string $voiceName;

    public function __construct()
    {
        $this->credentialsPath = $_ENV['GOOGLE_APPLICATION_CREDENTIALS'] ?? '';
        $this->languageCode = $_ENV['TTS_LANGUAGE_CODE'] ?? 'es-ES';
        $this->voiceName = $_ENV['TTS_VOICE'] ?? null;

        if (empty($this->credentialsPath) || !is_readable($this->credentialsPath)) {
            throw new RuntimeException(
                "El archivo de credenciales para Text-to-Speech no se encontró o no se puede leer en '{$this->credentialsPath}'."
            );
        }
    }

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

        if ($birthday && date('m-d') === date('m-d', strtotime($birthday))) {
            $parts[] = '. ¡Feliz cumpleaños!';
        }

        return implode(' ', $parts) . '.';
    }

    public function synthesizeGreeting(string $text): string
    {
        try {
            $client = new TextToSpeechClient(['credentials' => $this->credentialsPath]);
            $input = (new SynthesisInput())->setText($text);
            $voice = (new VoiceSelectionParams())->setLanguageCode($this->languageCode);
            if ($this->voiceName) {
                $voice->setName($this->voiceName);
            }
            $audioConfig = (new AudioConfig())->setAudioEncoding(AudioEncoding::MP3);
            $response = $client->synthesizeSpeech(compact('input', 'voice', 'audioConfig'));
            $client->close();
            $base64Audio = base64_encode($response->getAudioContent());
            return sprintf('<audio controls autoplay src="data:audio/mp3;base64,%s">Tu navegador no soporta audio HTML5.</audio>', $base64Audio);
        } catch (Throwable $e) {
            error_log('Error en síntesis de voz de Google: ' . $e->getMessage());
            return '❌ Error en síntesis de voz: ' . htmlspecialchars($e->getMessage());
        }
    }
}
