<?php

header('Content-Type: application/json');
require_once __DIR__ . '/../controlador/estacionesControlador.php';

$conn = conexion(); // conexión PDO
$controller = new EstacionController($conn);

$accion = $_GET['accion'] ?? '';
$input = json_decode(file_get_contents('php://input'), true);

switch($accion) {
    case 'crear':
        echo json_encode($controller->crearEstacion($input));
        break;
    case 'modificar':
        echo json_encode($controller->modificarEstacion($input));
        break;
    case 'listar':
        echo json_encode($controller->obtenerEstaciones());
        break;
    case 'eliminar':
        echo json_encode($controller->eliminarEstacion($input));
        break;
    default:
        echo json_encode(['success' => false, 'error' => 'Acción no válida']);
}
