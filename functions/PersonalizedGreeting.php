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
            } catch (Exception $e) {
                $this->client = null;
            }
        }
    }

    public function synthesize(string $text): string
    {
        if ($this->client === null) {
            return '';
        }

        try {
            $input = new SynthesisInput(['text' => $text]);
            $voice = new VoiceSelectionParams([
                'language_code' => 'es-ES',
                'name' => 'es-ES-Standard-A'
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
            return "<audio autoplay style=\"display:none\"><source src='$src' type='audio/mpeg'></audio>";
        } catch (Exception $e) {
            return '';
        }
    }
}
