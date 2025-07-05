<?php
// Se incluyen todas las clases necesarias de Google y Azure.
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
    private ?TextToSpeechClient $googleClient = null;

    public function __construct(string $provider = 'google')
    {
        $this->provider = strtolower($provider);

        // Lógica de inicialización solo para Google, ya que el SDK de Azure no necesita un cliente persistente.
        if ($this->provider === 'google') {
            $credentials = getenv('TTS_CREDENTIALS_PATH');
            if ($credentials && file_exists($credentials)) {
                putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $credentials);
                try {
                    $this->googleClient = new TextToSpeechClient();
                } catch (\Exception $e) {
                    $this->googleClient = null; // Falla la inicialización, se manejará en el método de síntesis.
                }
            }
        }
    }

    /**
     * Genera el audio codificado en base64 para el texto y género dados.
     * Devuelve null si falla.
     */
    public function synthesizeVoiceData(string $voiceText, string $gender = 'M'): ?string
    {
        if (trim($voiceText) === '') {
            return null;
        }

        // El selector de proveedor llama al método correspondiente.
        return match ($this->provider) {
            'azure'  => $this->synthesizeAzure($voiceText, $gender),
            default  => $this->synthesizeGoogle($voiceText, $gender),
        };
    }

    /**
     * Sintetiza voz usando Google Cloud. Devuelve audio en base64.
     */
    private function synthesizeGoogle(string $voiceText, string $gender): ?string
    {
        if ($this->googleClient === null) {
            return null;
        }

        try {
            $input = new SynthesisInput(['text' => $voiceText]);
            $languageCode = getenv('TTS_LANGUAGE_CODE') ?: 'es-ES';
            $gender = strtoupper($gender);

            $voiceName = ($gender === 'F')
                ? (getenv('TTS_VOICE_B') ?: 'es-ES-Wavenet-B')
                : (getenv('TTS_VOICE_A') ?: 'es-ES-Wavenet-A');

            $voice = new VoiceSelectionParams([
                'language_code' => $languageCode,
                'name' => $voiceName
            ]);

            $audioConfig = new AudioConfig(['audio_encoding' => AudioEncoding::MP3]);

            $request = new SynthesizeSpeechRequest([
                'input' => $input,
                'voice' => $voice,
                'audio_config' => $audioConfig
            ]);

            $response = $this->googleClient->synthesizeSpeech($request);
            $audioContent = $response->getAudioContent();

            // Devuelve el contenido en base64 o null si está vacío.
            return $audioContent ? base64_encode($audioContent) : null;
        } catch (\Exception $e) {
            // Error durante la llamada a la API.
            return null;
        }
    }

    /**
     * Sintetiza voz usando Azure Cognitive Services. Devuelve audio en base64.
     */
    private function synthesizeAzure(string $voiceText, string $gender): ?string
    {
        $key = getenv('SPEECH_KEY');
        $region = getenv('SPEECH_REGION');
        if (!$key || !$region) {
            return null; // Credenciales no configuradas.
        }

        try {
            $config = AzureSpeechConfig::fromSubscription($key, $region);
            $languageCode = getenv('TTS_LANGUAGE_CODE') ?: 'es-ES';
            $gender = strtoupper($gender);
            
            // Nombres de voces neuronales para Azure.
            $voiceName = ($gender === 'F')
                ? (getenv('AZURE_TTS_VOICE_B') ?: 'es-ES-ElviraNeural')
                : (getenv('AZURE_TTS_VOICE_A') ?: 'es-ES-AlvaroNeural');

            $config->setSpeechSynthesisLanguage($languageCode);
            $config->setSpeechSynthesisVoiceName($voiceName);

            $synth = new AzureSpeechSynthesizer($config);
            $result = $synth->speakTextAsync($voiceText)->get(); // Usar async y esperar el resultado.
            $audioData = $result->getAudioData();

            // Devuelve el contenido en base64 o null si está vacío.
            return $audioData ? base64_encode($audioData) : null;
        } catch (\Exception $e) {
            // Error durante la llamada a la API.
            return null;
        }
    }

    /**
     * Devuelve una etiqueta <audio> HTML para reproducir el TTS.
     * Retorna una cadena vacía si la síntesis falla.
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