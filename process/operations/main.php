<?php
/**
 * API Endpoint para registrar la asistencia de un usuario y devolver una respuesta JSON.
 * Versión optimizada y robusta.
 */

// --- INICIO DEL BLOQUE DE ARRANQUE ---
// Carga el entorno de la aplicación, incluyendo Composer y helpers.
require_once dirname(__DIR__, 2) . '/functions/autoload_helper.php';

try {
    require_vendor_autoload(dirname(__DIR__, 2));
    require_once dirname(__DIR__, 2) . '/functions/general.php';
} catch (RuntimeException $e) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit(1);
}

// Habilitar que MySQLi lance excepciones en lugar de solo advertencias.
// Esto permite que nuestro bloque try-catch atrape errores de conexión.
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Iniciar la sesión si no está activa.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// --- FIN DEL BLOQUE DE ARRANQUE ---

// Establecer la cabecera para indicar que la respuesta será en formato JSON.
header('Content-Type: application/json');

// Inicializar la respuesta por defecto.
$response = [
    'success' => false,
    'message' => 'Ocurrió un error inesperado en el servidor.',
    'greetingText' => '',
    'audioPlayer' => ''
];

// Variables de conexión que se usarán en el bloque principal.
$conn = null;
$koha = null;

try {
    // --- PASO 1: VALIDAR LA SOLICITUD ---
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['user_id'])) {
        throw new InvalidArgumentException('Solicitud no válida o ID de usuario no proporcionado.');
    }

    // --- PASO 2: ESTABLECER Y VERIFICAR CONEXIONES A LA BASE DE DATOS ---
    require_once dirname(__DIR__, 2) . '/functions/dbconn.php';
    
    // Conexiones a la BD establecidas en dbconn.php
    if (!isset($conn) || !$conn instanceof mysqli || $conn->connect_error) {
        throw new RuntimeException(
            "Error al conectar con la base de datos local: " . ($conn->connect_error ?? 'Variable no instanciada')
        );
    }

    if (!isset($koha) || !$koha instanceof mysqli || $koha->connect_error) {
        throw new RuntimeException(
            "Error al conectar con la base de datos de Koha: " . ($koha->connect_error ?? 'Variable no instanciada')
        );
    }

    // --- PASO 3: PROCESAR LOS DATOS DEL USUARIO ---
    $userId = strtoupper(sanitize($conn, $_POST['user_id']));
    $currentDate = date('Y-m-d');

    $stmt = $koha->prepare("SELECT CONCAT(firstname,' ',surname) AS fullname, borrowernumber, categorycode, dateofbirth, country FROM borrowers WHERE cardnumber = ? AND dateexpiry > ?");
    $stmt->bind_param('ss', $userId, $currentDate);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$user) {
        throw new Exception('Usuario no encontrado, inactivo o la tarjeta ha expirado.');
    }

    // --- PASO 4: REGISTRAR ASISTENCIA ---
    $attendance = handleAttendance($userId, $conn);
    if (!$attendance['success']) {
        throw new Exception($attendance['message']);
    }

    // --- PASO 5: CONSTRUIR LA RESPUESTA DE ÉXITO ---
    $response['success'] = true;
    $response['message'] = $attendance['message'];
    
    // Si es una entrada, generar saludo con voz.
    if ($attendance['status'] === 'IN') {
        require_once dirname(__DIR__, 2) . '/functions/PersonalizedGreeting.php';
        $greetingService = new PersonalizedGreeting();
        $timeOfDay = strtolower(get_time_of_day());

        $greetingText = $greetingService->buildGreeting($user['fullname'], $timeOfDay, $user['categorycode'], $user['dateofbirth'], $user['country']);
        $audioPlayer = $greetingService->synthesizeGreeting($greetingText);
        
        $response['greetingText'] = $greetingText;
        $response['audioPlayer'] = $audioPlayer;
    } else {
        // Si es una salida, el saludo es más simple y sin voz.
        $response['greetingText'] = "Hasta luego, " . htmlspecialchars($user['fullname']) . ".";
    }

} catch (Throwable $e) {
    // --- MANEJO CENTRALIZADO DE ERRORES ---
    // Cualquier error (de BD, de lógica, de la API de Google) será atrapado aquí.
    
    // Registrar el error técnico detallado en el log del servidor para el desarrollador.
    error_log("Error en API de asistencia (main.php): " . $e->getMessage());

    // Preparar un mensaje amigable para el usuario.
    $response['message'] = $e->getMessage();
    
    // Establecer el código de estado HTTP a 500 para indicar un error de servidor.
    http_response_code(500);

} finally {
    // --- CIERRE DE CONEXIONES ---
    // Cerrar las conexiones si fueron abiertas exitosamente.
    if ($conn instanceof mysqli) {
        $conn->close();
    }
    if ($koha instanceof mysqli) {
        $koha->close();
    }
}

// --- PASO FINAL: ENVIAR RESPUESTA ---
// Enviar siempre una respuesta JSON, ya sea de éxito o de error.
echo json_encode($response);


/**
 * Función de registro de asistencia principal.
 * Determina si es una entrada o salida y la guarda en `inout_log`.
 */
function handleAttendance(string $id, mysqli $conn): array
{
    $timestamp = date('Y-m-d H:i:s');
    $status = 'IN';

    $stmt = $conn->prepare("SELECT status FROM inout_log WHERE id = ? ORDER BY timestamp DESC LIMIT 1");
    $stmt->bind_param('s', $id);
    $stmt->execute();
    $lastRecord = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($lastRecord && $lastRecord['status'] === 'IN') {
        $status = 'OUT';
    }

    $stmt = $conn->prepare("INSERT INTO inout_log (id, timestamp, status) VALUES (?, ?, ?)");
    $stmt->bind_param('sss', $id, $timestamp, $status);
    $stmt->execute();
    $stmt->close();

    $message = $status === 'IN'
        ? "Entrada registrada a las " . date('g:i A', strtotime($timestamp))
        : "Salida registrada a las " . date('g:i A', strtotime($timestamp));

    return ['success' => true, 'status' => $status, 'message' => $message];
}
