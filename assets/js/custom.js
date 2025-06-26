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
  if (audio) {
    audio.addEventListener('ended', function () {
      window.location.replace('dash.php');
    });
  }
}

function playTTS(text) {
  $.ajax({
    url: 'tts.php',
    method: 'POST',
    data: { text: text },
    success: function (html) {
      $('body').append(html);
      attachAudioRedirect();
    }
  });
}
