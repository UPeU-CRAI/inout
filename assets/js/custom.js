var showNotification;
showNotification = function(from, align, message, color) {
  // type = ['', 'info', 'danger', 'success', 'warning', 'rose', 'primary'];
  // color = Math.floor((Math.random() * 6) + 1);
  // color = 4;
  $.notify({
    icon: "add_alert",
    message: message

  }, {
    type: color,
    delay: 3000,
    placement: {
      from: from,
      align: align
    }
  });
}

function attachAudioRedirect() {
  const audio = document.getElementById('tts-audio');
  if (!audio) return;

  let done = false;

  const cleanupAndRedirect = function () {
    if (done) return;
    done = true;

    audio.remove();

    const ttsText = document.getElementById('tts-text');
    if (ttsText) {
      ttsText.textContent = '';
    }

    window.location.replace('dash.php');
  };

  audio.addEventListener('ended', cleanupAndRedirect);

  // Fallback in case the 'ended' event does not fire
  setTimeout(cleanupAndRedirect, 10000);
}

