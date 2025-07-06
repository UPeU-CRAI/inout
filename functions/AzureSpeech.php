<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/env_loader.php';

use Microsoft\CognitiveServices\Speech\SpeechConfig;
use Microsoft\CognitiveServices\Speech\SpeechSynthesizer;
use Microsoft\CognitiveServices\Speech\ResultReason;

class AzureSpeech
{
    private ?SpeechSynthesizer $synthesizer = null;

    public function __construct()
    {
        $key = getenv('SPEECH_KEY');
        $region = getenv('SPEECH_REGION');
        if ($key && $region) {
            try {
                $speechConfig = SpeechConfig::fromSubscription($key, $region);
                $this->synthesizer = new SpeechSynthesizer($speechConfig);
            } catch (\Exception $e) {
                $this->synthesizer = null;
            }
        }
    }

    public function synthesizeVoice(string $voiceText, string $gender = 'M'): string
    {
        if ($this->synthesizer === null || trim($voiceText) === '') {
            return '';
        }

        $gender = strtoupper($gender);
        if ($gender === 'F') {
            $voiceName = getenv('AZURE_TTS_VOICE_B') ?: 'en-US-JennyNeural';
        } else {
            $voiceName = getenv('AZURE_TTS_VOICE_A') ?: 'en-US-AndrewNeural';
        }

        try {
            $config = $this->synthesizer->getConfig();
            $config->setSpeechSynthesisVoiceName($voiceName);
            $result = $this->synthesizer->speakText($voiceText);
            if ($result->getReason() !== ResultReason::SynthesizingAudioCompleted) {
                return '';
            }
            $audioData = $result->getAudioData();
            if (!$audioData) {
                return '';
            }
            $b64 = base64_encode($audioData);
            $src = "data:audio/mpeg;base64,$b64";
            return "<audio id=\"tts-audio\" autoplay style=\"display:none\"><source src=\"$src\" type=\"audio/mpeg\"></audio>";
        } catch (\Exception $e) {
            return '';
        }
    }
}
