/**
 * Muestra una notificación en pantalla usando el plugin bootstrap-notify.
 * @param {string} from    Posición vertical (ej. 'top', 'bottom')
 * @param {string} align   Alineación horizontal (ej. 'left', 'center', 'right')
 * @param {string} message El mensaje a mostrar (puede contener HTML).
 * @param {string} color   El tipo de notificación (ej. 'success', 'danger', 'info', 'warning').
 */
var showNotification = function(from, align, message, color) {
  // Aseguramos que el tipo de color sea uno de los válidos por bootstrap-notify
  const validTypes = ['info', 'danger', 'success', 'warning', 'primary', 'rose'];
  const notificationType = validTypes.includes(color) ? color : 'primary';

  $.notify({
    icon: "add_alert", // Icono de Material Design
    message: message
  }, {
    type: notificationType,
    timer: 3000, // La notificación se cerrará después de 3 segundos
    delay: 1000,
    placement: {
      from: from,
      align: align
    }
  });
};

// --- Lógica Principal de la Aplicación ---
// Se ejecuta cuando el documento HTML está completamente cargado.
$(document).ready(function() {

  // Enfocar automáticamente el campo de entrada al cargar la página.
  $('#user_id').focus();

  /**
   * Maneja el evento de envío del formulario de asistencia.
   */
  $("#attendance").submit(function(e) {
    // Prevenir el comportamiento por defecto del formulario (que recargaría la página).
    e.preventDefault();

    var userid = $("#user_id").val().trim();

    // 1. Validación simple: no enviar si el campo está vacío.
    if (userid === "") {
      showNotification('top', 'center', 'Por favor, ingrese o escanee un ID de usuario.', 'warning');
      return;
    }

    // Limpiamos los resultados de la consulta anterior.
    $('#user-info-spot').html('');

    // 2. Ejecutar la llamada AJAX para comunicarse con el servidor.
    $.ajax({
      type: "POST",
      url: "process/operations/main.php", // La URL de nuestro endpoint PHP
      data: {
        user_id: userid
      },
      dataType: 'json', // Esperamos una respuesta en formato JSON
      
      // La función 'success' se ejecuta si la petición AJAX tiene éxito (HTTP 200)
      success: function(response) {
        if (response.success) {
          // 3. ÉXITO: El servidor procesó la asistencia correctamente.
          
          // Mostramos el saludo y el mensaje de estado en una notificación.
          // Si es una salida, response.greetingText contendrá "Hasta luego...".
          showNotification('top', 'center', response.greetingText + '<br>' + response.message, 'success');

          // Si el servidor envió un reproductor de audio (porque fue una entrada), lo mostramos.
          if (response.audioPlayer) {
            $('#user-info-spot').html(response.audioPlayer);
          }

        } else {
          // 4. ERROR: El servidor devolvió un error de lógica (ej. usuario no encontrado).
          showNotification('top', 'center', response.message, 'danger');
        }
      },
      
      // La función 'error' se ejecuta si hay un problema con la comunicación (ej. error 500, 404).
      error: function(xhr, status, error) {
        console.error("Error en la llamada AJAX: ", status, error);
        showNotification('top', 'center', 'No se pudo comunicar con el servidor. Revise la conexión.', 'danger');
      },

      // La función 'complete' se ejecuta siempre al finalizar la llamada (éxito o error).
      complete: function() {
        // Limpiamos y enfocamos el campo de entrada para el siguiente registro.
        $("#user_id").val('').focus();
      }
    });
  });

});
