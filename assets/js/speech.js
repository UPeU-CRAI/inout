function speakMessage(message) {
  if (!message || !('speechSynthesis' in window)) return;
  var utterance = new SpeechSynthesisUtterance(message);
  window.speechSynthesis.cancel();
  window.speechSynthesis.speak(utterance);
}

