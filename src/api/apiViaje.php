<?php
session_start();
require_once '../conexion.php'; 

header('Content-Type: application/json');

if (!isset($_SESSION['id_usuario'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autenticado']);
    exit;
}

$id_usuario = $_SESSION['id_usuario'];
$data = json_decode(file_get_contents('php://input'), true);

$id_vehiculo = isset($data['id_vehiculo']) ? intval($data['id_vehiculo']) : null;
$estaciones  = isset($data['estaciones']) ? $data['estaciones'] : [];
$origen      = isset($data['origen']) ? $data['origen'] : 'N/A';
$destino     = isset($data['destino']) ? $data['destino'] : 'N/A';
$fecha_viaje = date('Y-m-d');

if (!$id_vehiculo || empty($estaciones)) {
    http_response_code(400);
    echo json_encode(['error' => 'Datos incompletos']);
    exit;
}

$conn = conexion();
$conn->beginTransaction();

try {
    // 1) Insertar viaje
    $sqlViaje = "INSERT INTO viaje (fecha_v, origen, destino) VALUES (?, ?, ?)";
    $stmtViaje = $conn->prepare($sqlViaje);
    $stmtViaje->execute([$fecha_viaje, $origen, $destino]);
    $id_viaje = $conn->lastInsertId();

    // 2) Vincular viaje con vehículo
    $sqlSeHace = "INSERT INTO sehace (id_VJE, id_V) VALUES (?, ?)";
    $stmtSeHace = $conn->prepare($sqlSeHace);
    $stmtSeHace->execute([$id_viaje, $id_vehiculo]);

    $ids_reservas = [];
    $orden = 1;

    foreach ($estaciones as $est) {
        $id_estacion = intval($est['id_estacion']);

        // 3) Insertar reserva
        $sqlReserva = "INSERT INTO reserva (nombre, estado, creado_at) VALUES (?, 'pendiente', ?)";
        $stmtReserva = $conn->prepare($sqlReserva);
        $stmtReserva->execute(["Reserva estación $id_estacion", $fecha_viaje]);
        $id_reserva = $conn->lastInsertId();
        $ids_reservas[] = $id_reserva;

        // 4) Asociar reserva a estación
        $sqlGuardar = "INSERT INTO guardareserva (id_R, id_E) VALUES (?, ?)";
        $stmtGuardar = $conn->prepare($sqlGuardar);
        $stmtGuardar->execute([$id_reserva, $id_estacion]);

        // 5) Asociar reserva a usuario
        $sqlHacer = "INSERT INTO hacereserva (id_R, id_U) VALUES (?, ?)";
        $stmtHacer = $conn->prepare($sqlHacer);
        $stmtHacer->execute([$id_reserva, $id_usuario]);

        // 6) Insertar parada
        $sqlParada = "INSERT INTO parada (orden, id_V) VALUES (?, ?)";
        $stmtParada = $conn->prepare($sqlParada);
        $stmtParada->execute([$orden, $id_viaje]);
        $id_parada = $conn->lastInsertId();

        // 7) Vincular parada con reserva
        $sqlMediante = "INSERT INTO mediante (id_P, id_R) VALUES (?, ?)";
        $stmtMediante = $conn->prepare($sqlMediante);
        $stmtMediante->execute([$id_parada, $id_reserva]);

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
