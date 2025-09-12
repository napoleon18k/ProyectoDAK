<?php
session_start(); 
require_once '../conexion.php'; // Importa el archivo con la función de conexión a la BD

// Indicamos que la respuesta será en formato JSON
header('Content-Type: application/json');

// 📌 Guardamos en un archivo de log información de la sesión y de los datos recibidos vía POST (para debug)
file_put_contents(
    'log_viaje.txt', 
    "SESSION: " . print_r($_SESSION, true) . "\nPOST: " . file_get_contents('php://input') . "\n", 
    FILE_APPEND
);

// 📌 Verificamos que el usuario esté logueado (se espera que exista id_usuario en la sesión)
if (!isset($_SESSION['id_usuario'])) {
    http_response_code(401); // Código HTTP 401: No autorizado
    echo json_encode(['error' => 'No autenticado']);
    exit; // Detenemos la ejecución
}

$id_usuario = $_SESSION['id_usuario']; // Guardamos el ID del usuario logueado

// 📌 Obtenemos los datos enviados en el cuerpo de la petición (JSON → array asociativo)
$data = json_decode(file_get_contents('php://input'), true);

// Variables principales del viaje/reserva
$id_vehiculo = isset($data['id_vehiculo']) ? intval($data['id_vehiculo']) : null;
$id_estacion = isset($data['id_estacion']) ? intval($data['id_estacion']) : null;
$fecha_inicio = date('Y-m-d H:i:s'); // Fecha actual del inicio del viaje

// 📌 Validamos que se hayan recibido todos los datos requeridos
if (!$id_vehiculo || !$id_estacion) {
    http_response_code(400); // Código HTTP 400: Solicitud incorrecta
    error_log('Datos incompletos: id_vehiculo=' . var_export($id_vehiculo, true) . ', id_estacion=' . var_export($id_estacion, true));

    // Respondemos con detalles del error
    echo json_encode([
        'error' => 'Datos incompletos',
        'id_vehiculo' => $id_vehiculo,
        'id_estacion' => $id_estacion,
        'detalle' => 'Verifica que el vehículo y la estación estén seleccionados correctamente.'
    ]);
    exit;
}

$conn = conexion(); // Conexión a la base de datos
$conn->beginTransaction(); // Iniciamos transacción para asegurar consistencia

try {
    // 📌 1) Insertamos un nuevo registro en la tabla "viaje"
    $sqlViaje = "INSERT INTO viaje (id_vehiculo, fecha_inicio) VALUES (?, ?)";
    $stmtViaje = $conn->prepare($sqlViaje);
    $stmtViaje->execute([$id_vehiculo, $fecha_inicio]);
    $id_viaje = $conn->lastInsertId(); // Obtenemos el ID del viaje recién creado
    $stmtViaje = null; // Cerramos statement

    // 📌 2) Insertamos un nuevo registro en la tabla "reserva" con estado 'pendiente'
    $sqlReserva = "INSERT INTO reserva (id_usuario, id_estacion, fecha_reserva, estado) 
                   VALUES (?, ?, ?, 'pendiente')";
    $stmtReserva = $conn->prepare($sqlReserva);
    $stmtReserva->execute([$id_usuario, $id_estacion, $fecha_inicio]);
    $id_reserva = $conn->lastInsertId(); // Obtenemos el ID de la reserva creada
    $stmtReserva = null; // Cerramos statement

    // 📌 Confirmamos la transacción (viaje y reserva guardados con éxito)
    $conn->commit();

    // Respondemos con éxito en formato JSON
    echo json_encode([
        'success' => true,
        'id_viaje' => $id_viaje,
        'id_reserva' => $id_reserva,
        'message' => '¡Viaje y reserva guardados correctamente!'
    ]);
} catch (Exception $e) {
    // 📌 Si algo falla, revertimos los cambios
    $conn->rollBack();
    http_response_code(500); // Error interno del servidor
    echo json_encode(['error' => 'Error al guardar', 'detalle' => $e->getMessage()]);
}

// Cerramos conexión
$conn = null;
?>
