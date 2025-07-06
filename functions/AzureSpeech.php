<?php
/**
 * Azure Text-to-Speech via REST API
 * Este archivo reemplaza el uso del SDK oficial que no cuenta con soporte
 * para PHP. Utiliza cURL para obtener el token y generar el audio.
 */
require_once __DIR__ . '/env_loader.php';

class AzureSpeech
{
    /** @var string|null */
    private ?string $subscriptionKey;
    /** @var string|null */
    private ?string $region;

    public function __construct(?string $key = null, ?string $region = null)
    {
        $this->subscriptionKey = $key ?: getenv('SPEECH_KEY');
        $this->region = $region ?: getenv('SPEECH_REGION');
    }

    /**
     * Devuelve un tag <audio> con el resultado o cadena vacía si ocurre un error.
     * Mantiene el mismo nombre para compatibilidad con el código existente.
     *
     * @param string $voiceText Texto a sintetizar.
     * @param string $gender    "F" para voz femenina, "M" para masculina.
     * @return string HTML <audio> con el audio embebido o vacío en caso de fallo.
     */
    public function synthesizeVoice(string $voiceText, string $gender = 'M'): string
    {
        if (trim($voiceText) === '' || !$this->subscriptionKey || !$this->region) {
            return '';
        }

        $gender = strtoupper($gender);
        $voiceName = $gender === 'F'
            ? (getenv('AZURE_TTS_VOICE_B') ?: 'en-US-JennyNeural')
            : (getenv('AZURE_TTS_VOICE_A') ?: 'en-US-AndrewNeural');
        $language = getenv('AZURE_TTS_LANGUAGE_CODE') ?: substr($voiceName, 0, 5);
        $format = getenv('AZURE_TTS_FORMAT') ?: 'audio-16khz-32kbitrate-mono-mp3';

        $token = $this->fetchToken();
        if (!$token) {
            return '';
        }

        $ssmlText = "<speak version='1.0' xml:lang='{$language}'>" .
            "<voice name='{$voiceName}'>" .
            htmlspecialchars($voiceText, ENT_QUOTES | ENT_XML1) .
            '</voice></speak>';

        $url = "https://{$this->region}.tts.speech.microsoft.com/cognitiveservices/v1";
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $ssmlText,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/ssml+xml',
                "X-Microsoft-OutputFormat: {$format}",
                "Authorization: Bearer {$token}",
                'User-Agent: InOut-Azure-TTS'
            ],
        ]);
        $audio = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($audio === false || $code >= 400) {
            return '';
        }

        $b64 = base64_encode($audio);
        $src = "data:audio/mpeg;base64,{$b64}";
        return "<audio id=\"tts-audio\" autoplay style=\"display:none\"><source src=\"{$src}\" type=\"audio/mpeg\"></audio>";
    }

    /**
     * Obtiene un token de autenticación de Azure Speech.
     *
     * @return string|false Token o false si falla.
     */
    private function fetchToken(): string|false
    {
        if (!$this->subscriptionKey || !$this->region) {
            return false;
        }
        $url = "https://{$this->region}.api.cognitive.microsoft.com/sts/v1.0/issueToken";
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ["Ocp-Apim-Subscription-Key: {$this->subscriptionKey}"],
        ]);
        $token = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($token === false || $code >= 400) {
            return false;
        }
        return trim($token);
    }
}
