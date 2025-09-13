<?php
session_start();
require_once '../conexion.php'; 

header('Content-Type: application/json');

// 📌 Debug opcional
file_put_contents(
    'log_viaje.txt',
    "SESSION: " . print_r($_SESSION, true) . "\nPOST: " . file_get_contents('php://input') . "\n",
    FILE_APPEND
);

// 📌 Verificación de sesión
if (!isset($_SESSION['id_usuario'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autenticado']);
    exit;
}

$id_usuario = $_SESSION['id_usuario'];

// 📌 Datos recibidos
$data = json_decode(file_get_contents('php://input'), true);

$id_vehiculo = isset($data['id_vehiculo']) ? intval($data['id_vehiculo']) : null;
$estaciones  = isset($data['estaciones']) ? $data['estaciones'] : []; // 👈 ahora es un array
$origen      = isset($data['origen']) ? $data['origen'] : 'N/A';
$destino     = isset($data['destino']) ? $data['destino'] : 'N/A';
$fecha_viaje = date('Y-m-d H:i:s');

// 📌 Validación
if (!$id_vehiculo || empty($estaciones)) {
    http_response_code(400);
    echo json_encode([
        'error' => 'Datos incompletos',
        'detalle' => [
            'id_vehiculo' => $id_vehiculo,
            'estaciones'  => $estaciones
        ]
    ]);
    exit;
}

$conn = conexion();
$conn->beginTransaction();

try {
    // 1) Insertar viaje
    $sqlViaje = "INSERT INTO viajes (id_usuario, id_vehiculo, fecha_viaje, origen, destino) 
                 VALUES (?, ?, ?, ?, ?)";
    $stmtViaje = $conn->prepare($sqlViaje);
    $stmtViaje->execute([$id_usuario, $id_vehiculo, $fecha_viaje, $origen, $destino]);
    $id_viaje = $conn->lastInsertId();

    $ids_reservas = [];

    // 2) Insertar reservas + paradas
    $orden = 1;
    foreach ($estaciones as $est) {
        $id_estacion = intval($est['id_estacion']);

        // Insertar reserva
        $sqlReserva = "INSERT INTO reservas (id_usuario, id_estacion, fecha_reserva, estado) 
                       VALUES (?, ?, ?, 'pendiente')";
        $stmtReserva = $conn->prepare($sqlReserva);
        $stmtReserva->execute([$id_usuario, $id_estacion, $fecha_viaje]);
        $id_reserva = $conn->lastInsertId();

        $ids_reservas[] = $id_reserva;

        // Insertar parada vinculada al viaje y la reserva
        $sqlParada = "INSERT INTO paradas (id_viaje, id_reserva, orden) VALUES (?, ?, ?)";
        $stmtParada = $conn->prepare($sqlParada);
        $stmtParada->execute([$id_viaje, $id_reserva, $orden]);

        $orden++;
    }

    $conn->commit();

    echo json_encode([
        'success' => true,
        'id_viaje' => $id_viaje,
        'reservas' => $ids_reservas,
        'message' => '¡Viaje, reservas y paradas guardados correctamente!'
    ]);

} catch (Exception $e) {
    $conn->rollBack();
    http_response_code(500);
    echo json_encode(['error' => 'Error al guardar', 'detalle' => $e->getMessage()]);
}

$conn = null;
?>
