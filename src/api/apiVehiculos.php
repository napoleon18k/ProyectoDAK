<?php
require_once __DIR__ . '/../controlador/vehiculosControlador.php'; // Ya está correcto, sigue dentro de src

// Iniciar sesión para que los controladores puedan leer `$_SESSION['id_usuario']`
if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'OPTIONS':
        // Responde a preflight CORS
        http_response_code(200);
        exit;
    case 'GET':
        echo listarVehiculos();
        break;
    case 'POST':
        parse_str(file_get_contents("php://input"), $_POST);
        echo insertarVehiculos();
        break;
    case 'PUT':
        parse_str(file_get_contents("php://input"), $_PUT);
        echo modificarVehiculos($_PUT);
        break;
    case 'DELETE':
        parse_str(file_get_contents("php://input"), $_DELETE);
        echo eliminarVehiculos($_DELETE);
        break;
    default:
        echo json_encode(['error' => 'Método no soportado']);
}
?>