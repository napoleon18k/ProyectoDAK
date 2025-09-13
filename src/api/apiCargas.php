<?php
require_once __DIR__ . '/../controlador/estacionesControlador.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'OPTIONS':
        http_response_code(200);
        exit;

    case 'GET':
        if (isset($_GET['listar'])) {
            echo listarEstaciones();
        } else {
            echo json_encode(['error' => 'Acción GET no soportada']);
        }
        break;

    case 'POST':
        if (isset($_POST['insertar'])) {
            echo insertarEstaciones();
        } else {
            echo json_encode(['error' => 'Acción POST no soportada']);
        }
        break;

    case 'PUT':
        parse_str(file_get_contents("php://input"), $_PUT);
        echo modificarEstaciones($_PUT);
        break;

    case 'DELETE':
        parse_str(file_get_contents("php://input"), $_DELETE);
        echo eliminarEstaciones($_DELETE);
        break;

    default:
        echo json_encode(['error' => 'Método no soportado']);
}
