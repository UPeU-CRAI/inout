<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Google\Cloud\TextToSpeech\V1\AudioConfig;
use Google\Cloud\TextToSpeech\V1\AudioEncoding;
use Google\Cloud\TextToSpeech\V1\SynthesisInput;
use Google\Cloud\TextToSpeech\V1\TextToSpeechClient;
use Google\Cloud\TextToSpeech\V1\VoiceSelectionParams;
use Dotenv\Dotenv;

class PersonalizedGreeting
{
    public readonly string $languageCode;
    public readonly string $credentialsPath;
    public readonly ?string $voiceName;

    public function __construct()
    {
        $dotenv = Dotenv::createImmutable(dirname(__DIR__));
        $dotenv->load();

        $this->credentialsPath = $_ENV['GOOGLE_APPLICATION_CREDENTIALS'] ?? '';
        $this->languageCode = $_ENV['TTS_LANGUAGE_CODE'] ?? 'es-ES';
        $this->voiceName = $_ENV['TTS_VOICE'] ?? null;

        if (empty($this->credentialsPath) || !is_readable($this->credentialsPath)) {
            throw new RuntimeException(
                "Text-to-Speech credentials file not found or unreadable at '{$this->credentialsPath}'. " .
                'Check the GOOGLE_APPLICATION_CREDENTIALS variable in your .env file.'
            );
        }
    }

    public function buildGreeting(string $name, string $timeOfDay, string $category, ?string $birthday = null, ?string $country = null): string
    {
        $greetingPrefix = match (strtolower($timeOfDay)) {
            'morning' => 'Buenos días',
            'afternoon' => 'Buenas tardes',
            'evening', 'night' => 'Buenas noches',
            default => 'Hola',
        };
        $parts = [$greetingPrefix, $name];
        if ($category === 'DOCEN') { $parts[] = ', docente'; }
        elseif ($category === 'VISITA') { $parts[] = ', visitante'; }
        if ($country) { $parts[] = 'de ' . $country; }
        if ($birthday && date('m-d') === date('m-d', strtotime($birthday))) { $parts[] = '. ¡Feliz cumpleaños!'; }
        return implode(' ', $parts) . '.';
    }

    public function synthesizeGreeting(string $text): string
    {
        try {
            $client = new TextToSpeechClient(['credentials' => $this->credentialsPath]);
            $input = (new SynthesisInput())->setText($text);
            $voice = (new VoiceSelectionParams())->setLanguageCode($this->languageCode);
            if ($this->voiceName) { $voice->setName($this->voiceName); }
            $audioConfig = (new AudioConfig())->setAudioEncoding(AudioEncoding::MP3);
            $response = $client->synthesizeSpeech(compact('input', 'voice', 'audioConfig'));
            $client->close();
            $audioContent = $response->getAudioContent();
            $base64Audio = base64_encode($audioContent);
            // Dejamos los 'controls' por si el navegador bloquea el autoplay, así puedes darle play manualmente.
            return '<audio autoplay controls><source src="data:audio/mpeg;base64,' . $base64Audio . '" type="audio/mpeg">Tu navegador no soporta audio.</audio>';
        } catch (\Throwable $e) {
            return '❌ Error en síntesis de voz: ' . htmlspecialchars($e->getMessage());
        }
    }
}
