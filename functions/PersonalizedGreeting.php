<?php
class PersonalizedGreeting
{
    private $languageCode;
    private $credentialsPath;
    private $voiceName;

    public function __construct()
    {
        $envPath = dirname(__DIR__) . '/.env';
        $env = file_exists($envPath) ? parse_ini_file($envPath, false, INI_SCANNER_TYPED) : [];
        $this->credentialsPath = $env['TTS_CREDENTIALS_PATH'] ?? '';
        $this->languageCode = $env['TTS_LANGUAGE_CODE'] ?? 'es-ES';
        $this->voiceName = $env['TTS_VOICE'] ?? null;
    }

    public function buildGreeting(string $name, string $timeOfDay, string $category, ?string $birthday = null, ?string $country = null): string
    {
        $greeting = '';
        switch (strtolower($timeOfDay)) {
            case 'morning':
                $greeting .= 'Buenos días';
                break;
            case 'afternoon':
                $greeting .= 'Buenas tardes';
                break;
            case 'evening':
            case 'night':
                $greeting .= 'Buenas noches';
                break;
            default:
                $greeting .= 'Hola';
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

    public function synthesizeGreeting(string $text): string
    {
        if (!class_exists('Google\\Cloud\\TextToSpeech\\V1\\TextToSpeechClient')) {
            throw new RuntimeException('Google Cloud Text-to-Speech library not found.');
        }

        $client = new Google\Cloud\TextToSpeech\V1\TextToSpeechClient([
            'credentials' => $this->credentialsPath,
        ]);

        $input = (new Google\Cloud\TextToSpeech\V1\SynthesisInput())
            ->setText($text);

        $voice = (new Google\Cloud\TextToSpeech\V1\VoiceSelectionParams())
            ->setLanguageCode($this->languageCode);
        if ($this->voiceName) {
            $voice->setName($this->voiceName);
        }

        $audioConfig = (new Google\Cloud\TextToSpeech\V1\AudioConfig())
            ->setAudioEncoding(Google\Cloud\TextToSpeech\V1\AudioEncoding::MP3);

        $response = $client->synthesizeSpeech($input, $voice, $audioConfig);
        $audioContent = $response->getAudioContent();
        $client->close();

        $base64Audio = base64_encode($audioContent);
        return '<audio controls autoplay src="data:audio/mp3;base64,' . $base64Audio . '"></audio>';
    }
}
