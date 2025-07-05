<?php
use Google\Cloud\TextToSpeech\V1\Client\TextToSpeechClient;
use Google\Cloud\TextToSpeech\V1\SynthesisInput;
use Google\Cloud\TextToSpeech\V1\VoiceSelectionParams;
use Google\Cloud\TextToSpeech\V1\AudioConfig;
use Google\Cloud\TextToSpeech\V1\AudioEncoding;
use Google\Cloud\TextToSpeech\V1\SynthesizeSpeechRequest;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/env_loader.php';

class PersonalizedGreeting
{
    private ?TextToSpeechClient $client = null;

    public function __construct()
    {
        $credentials = getenv('TTS_CREDENTIALS_PATH');
        if ($credentials && file_exists($credentials)) {
            putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $credentials);
            try {
                $this->client = new TextToSpeechClient();
            } catch (\Exception $e) {
                $this->client = null;
            }
        }
    }

    /**
     * Generate base64 encoded audio for the given text and gender.
     * Returns null on failure.
     */
    public function synthesizeVoiceData(string $voiceText, string $gender = 'M'): ?string
    {
        if ($this->client === null || trim($voiceText) === '') {
            return null;
        }

        try {
            $input = new SynthesisInput(['text' => $voiceText]);

            $languageCode = getenv('TTS_LANGUAGE_CODE') ?: 'es-ES';

            $gender = strtoupper($gender);
            if ($gender === 'F') {
                $voiceName = getenv('TTS_VOICE_B') ?: getenv('TTS_VOICE') ?: 'es-ES-Wavenet-B';
            } else {
                $voiceName = getenv('TTS_VOICE_A') ?: getenv('TTS_VOICE') ?: 'es-ES-Wavenet-A';
            }

            $voice = new VoiceSelectionParams([
                'language_code' => $languageCode,
                'name' => $voiceName
            ]);
            $audioConfig = new AudioConfig([
                'audio_encoding' => AudioEncoding::MP3
            ]);
            $request = new SynthesizeSpeechRequest([
                'input' => $input,
                'voice' => $voice,
                'audio_config' => $audioConfig
            ]);
            $response = $this->client->synthesizeSpeech($request);
            $audioContent = $response->getAudioContent();
            if (!$audioContent) {
                return null;
            }
            return base64_encode($audioContent);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Devuelve un tag <audio> HTML para reproducir el TTS (o string vacío si falla).
     * Puedes pasar género ("F", "M", o cualquier valor) para seleccionar voz femenina/masculina.
     * 
     * @param string $voiceText  El texto que se leerá.
     * @param string $gender     "F" para femenino, "M" para masculino, otro para default.
     * @return string            HTML <audio> tag embebido con el audio generado, o vacío si falla.
     */
    public function synthesizeVoice(string $voiceText, string $gender = 'M'): string
    {
        $b64 = $this->synthesizeVoiceData($voiceText, $gender);
        if (!$b64) {
            return '';
        }

        $src = "data:audio/mpeg;base64,$b64";
        return "<audio id=\"tts-audio\" autoplay style=\"display:none\"><source src=\"$src\" type=\"audio/mpeg\"></audio>";
    }
}
