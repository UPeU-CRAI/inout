<?php
class PersonalizedGreeting {
    private $text;
    private $lang;
    public function __construct($text, $lang = 'es') {
        $this->text = $text;
        $this->lang = $lang;
    }
    public function getText() {
        return $this->text;
    }
    public function getAudioElement() {
        $url = 'https://translate.google.com/translate_tts?ie=UTF-8&client=tw-ob&tl=' . $this->lang . '&q=' . urlencode($this->text);
        return '<audio autoplay><source src="' . $url . '" type="audio/mpeg"></audio>';
    }
}
?>
