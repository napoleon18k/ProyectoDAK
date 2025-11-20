<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../modelo/modeloReserva.php';

if (!isset($_GET['cargador'])) {
    echo json_encode(["error" => "Falta parámetro cargador"]);
    exit;
}
if (!isset($_GET['fecha'])) {
    echo json_encode(["error" => "Falta parámetro fecha"]);
    exit;
}

$cargador = $_GET['cargador'];
$fecha = $_GET['fecha'];

// Crear instancia del modelo (NO usar llamada estática)
$modelo = new ReservaModelo();
$slots = $modelo->obtenerSlotsOcupados($cargador, $fecha);

echo json_encode([
    "ok" => true,
    "ocupados" => $slots
]);
