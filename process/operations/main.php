<?php
/**
 * API Endpoint para registrar la asistencia de un usuario y devolver una respuesta JSON.
 */

// Iniciar la sesión si no está activa. Es fundamental para los mensajes de estado.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Establecer la cabecera para indicar que la respuesta será en formato JSON.
header('Content-Type: application/json');

// Requerir archivos necesarios usando rutas relativas seguras.
// Se asume que koha_conn.php define la conexión $koha
require_once dirname(__DIR__, 2) . '/functions/dbconn.php'; 
require_once dirname(__DIR__, 2) . '/functions/general.php';
require_once dirname(__DIR__, 2) . '/functions/PersonalizedGreeting.php'; // Nuestra clase de saludos

/**
 * Función de registro de asistencia principal.
 * Determina si es una entrada o salida y la guarda en `inout_log`.
 * NOTA: Esta es tu función, integrada en este nuevo flujo.
 */
function handleAttendance(string $id, mysqli $conn): array
{
    $id = strtoupper(sanitize($conn, $id));
    $timestamp = date('Y-m-d H:i:s');
    
    // Buscar el último registro de este ID para determinar si es entrada o salida.
    $stmt = $conn->prepare("SELECT status FROM inout_log WHERE id = ? ORDER BY timestamp DESC LIMIT 1");
    $stmt->bind_param('s', $id);
    $stmt->execute();
    $lastRecord = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $status = 'IN'; // Por defecto, es una entrada.
    // Si hay un registro previo y fue de ENTRADA, el siguiente es de SALIDA.
    if ($lastRecord && $lastRecord['status'] === 'IN') {
        $status = 'OUT';
    }

    $message = $status === 'IN'
        ? "Entrada registrada a las " . date('g:i A', strtotime($timestamp))
        : "Salida registrada a las " . date('g:i A', strtotime($timestamp));

    // Insertar el nuevo registro.
    $stmt = $conn->prepare("INSERT INTO inout_log (id, timestamp, status) VALUES (?, ?, ?)");
    $stmt->bind_param('sss', $id, $timestamp, $status);
    $stmt->execute();
    $stmt->close();

    return [
        'success' => true,
        'status' => $status,
        'message' => $message,
    ];
}

// --- Inicio del Flujo Principal de la API ---

// 1. Inicializar la respuesta JSON por defecto.
$response = [
    'success' => false,
    'message' => 'Solicitud no válida o ID de usuario no proporcionado.',
    'greetingText' => '',
    'audioPlayer' => ''
];

// 2. Validar que la solicitud sea POST y que contenga el ID del usuario.
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['user_id'])) {
    echo json_encode($response);
    exit();
}

// 3. Obtener conexiones a la base de datos (se asume que dbconn.php las define).
$conn = get_db_connection(); // Conexión local
// Se asume que $koha se define en dbconn.php o un archivo similar
if (!isset($koha) || !$koha instanceof mysqli) {
    $response['message'] = 'Error: La conexión a la base de datos de Koha no está disponible.';
    echo json_encode($response);
    exit();
}

$userId = strtoupper(sanitize($conn, $_POST['user_id']));
$currentDate = date('Y-m-d');

try {
    // 4. Obtener datos del usuario desde Koha.
    $stmt = $koha->prepare("SELECT CONCAT(firstname,' ',surname) AS fullname, borrowernumber, categorycode, dateofbirth, country FROM borrowers WHERE cardnumber = ? AND dateexpiry > ?");
    $stmt->bind_param('ss', $userId, $currentDate);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$user) {
        $response['message'] = 'Usuario no encontrado, inactivo o la tarjeta ha expirado.';
        throw new Exception($response['message']);
    }

    // 5. Registrar la asistencia (ENTRADA/SALIDA).
    $attendanceResult = handleAttendance($userId, $conn);
    
    if (!$attendanceResult['success']) {
        throw new Exception($attendanceResult['message']);
    }
    
    $response['success'] = true;
    $response['message'] = $attendanceResult['message'];

    // 6. Generar saludo con voz SOLO si es una ENTRADA.
    if ($attendanceResult['status'] === 'IN') {
        $greetingService = new PersonalizedGreeting();
        
        $timeOfDay = strtolower(get_time_of_day()); // Función de general.php
        
        $greetingText = $greetingService->buildGreeting(
            $user['fullname'],
            $timeOfDay,
            $user['categorycode'],
            $user['dateofbirth'],
            $user['country']
        );
        
        $audioPlayer = $greetingService->synthesizeGreeting($greetingText);

        $response['greetingText'] = $greetingText;
        $response['audioPlayer'] = $audioPlayer;
        $_SESSION['success'] = "{$greetingText} <br> {$response['message']}";
    } else {
        // Si es una salida, el saludo es más simple y sin voz.
        $response['greetingText'] = "Hasta luego, " . $user['fullname'] . ".";
        $_SESSION['success'] = "{$response['greetingText']} <br> {$response['message']}";
    }

} catch (Exception $e) {
    // 7. Manejar cualquier error que haya ocurrido en el proceso.
    error_log("Error en process/operations/main.php: " . $e->getMessage());
    // El mensaje de la respuesta ya fue establecido en el punto del error.
    if (empty($response['message']) || $response['message'] === 'Solicitud no válida o ID de usuario no proporcionado.') {
        $response['message'] = 'Error del servidor: ' . htmlspecialchars($e->getMessage());
    }
    $_SESSION['error'] = $response['message'];

} finally {
    // 8. Cerrar conexiones y enviar la respuesta.
    $conn->close();
    $koha->close();
    echo json_encode($response);
}
