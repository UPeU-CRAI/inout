<?php
use Google\Cloud\TextToSpeech\V1\Client\TextToSpeechClient;
use Google\Cloud\TextToSpeech\V1\SynthesisInput;
use Google\Cloud\TextToSpeech\V1\VoiceSelectionParams;
use Google\Cloud\TextToSpeech\V1\AudioConfig;
use Google\Cloud\TextToSpeech\V1\AudioEncoding;
use Google\Cloud\TextToSpeech\V1\SynthesizeSpeechRequest;
use Microsoft\CognitiveServices\Speech\SpeechConfig as AzureSpeechConfig;
use Microsoft\CognitiveServices\Speech\SpeechSynthesizer as AzureSpeechSynthesizer;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/env_loader.php';

class PersonalizedGreeting
{
    private string $provider;
    private ?TextToSpeechClient $client = null;

    public function __construct(string $provider = 'google')
    {
        $this->provider = strtolower($provider);

        $credentials = getenv('TTS_CREDENTIALS_PATH');
        if ($this->provider === 'google' && $credentials && file_exists($credentials)) {
            putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $credentials);
            try {
                $this->client = new TextToSpeechClient();
            } catch (\Exception $e) {
                $this->client = null;
            }
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
        if (trim($voiceText) === '') {
            return '';
        }

        return match ($this->provider) {
            'azure'  => $this->synthesizeAzure($voiceText, $gender),
            default => $this->synthesizeGoogle($voiceText, $gender),
        };
    }

    private function synthesizeGoogle(string $voiceText, string $gender): string
    {
        if ($this->client === null) {
            return '';
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
                return '';
            }
            $b64 = base64_encode($audioContent);
            $src = "data:audio/mpeg;base64,$b64";
            return "<audio id=\"tts-audio\" autoplay style=\"display:none\"><source src=\"$src\" type=\"audio/mpeg\"></audio>";
        } catch (\Exception $e) {
            return '';
        }
    }

    private function synthesizeAzure(string $voiceText, string $gender): string
    {
        $key = getenv('SPEECH_KEY');
        $region = getenv('SPEECH_REGION');
        if (!$key || !$region) {
            return '';
        }

        try {
            $config = AzureSpeechConfig::fromSubscription($key, $region);
            $languageCode = getenv('TTS_LANGUAGE_CODE') ?: 'es-ES';
            $gender = strtoupper($gender);
            if ($gender === 'F') {
                $voiceName = getenv('TTS_VOICE_B') ?: 'es-ES-ElviraNeural';
            } else {
                $voiceName = getenv('TTS_VOICE_A') ?: 'es-ES-AlvaroNeural';
            }
            $config->setSpeechSynthesisLanguage($languageCode);
            $config->setSpeechSynthesisVoiceName($voiceName);
            $synth = new AzureSpeechSynthesizer($config);
            $result = $synth->speakText($voiceText);
            $audio = $result->getAudioData();
            if (!$audio) {
                return '';
            }
            $b64 = base64_encode($audio);
            $src = "data:audio/mpeg;base64,$b64";
            return "<audio id=\"tts-audio\" autoplay style=\"display:none\"><source src=\"$src\" type=\"audio/mpeg\"></audio>";
        } catch (\Exception $e) {
            return '';
        }
    }
}
