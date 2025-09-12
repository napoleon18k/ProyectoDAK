<?php
require_once __DIR__ . '/../controlador/usuarioControlador.php';
session_start();

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
        if (isset($_GET['listar'])) {
            echo listarUsuarios();
        } else {
            echo json_encode(['error' => 'Acción GET no soportada']);
        }
        break;

    case 'POST':
        if (isset($_POST['login'])) {
            $usuario = $_POST['usuario'] ?? '';
            $password = $_POST['password'] ?? '';

            if (empty($usuario) || empty($password)) {
                echo json_encode(['success' => false, 'error' => 'Usuario o contraseña vacíos']);
                exit();
            }

            $result = $usuarioObj->login($usuario, $password);

            if ($result['success']) {
                $_SESSION['usuario'] = $usuario;
                $_SESSION['rol'] = $result['rol'];

                echo json_encode(['success' => true, 'rol' => $result['rol']]);
            } else {
                echo json_encode(['success' => false, 'error' => $result['error'] ?? 'Usuario o contraseña incorrecta']);
            }
        } elseif (isset($_POST['insertar'])) {
            echo insertarUsuario();
        } else {
            echo json_encode(['error' => 'Acción POST no soportada']);
        }
        break;

    case 'PUT':
        parse_str(file_get_contents("php://input"), $_PUT);

        // 🔹 Usar usuario de la sesión
        if (!isset($_SESSION['usuario'])) {
            echo json_encode(['success' => false, 'error' => 'No hay usuario en sesión']);
            exit();
        }

        $data = [
            'usuario' => $_PUT['usuario'] ?? null,
            'correo'  => $_PUT['correo'] ?? null,
            'password'=> $_PUT['password'] ?? null,
            'usuario_sesion' => $_SESSION['usuario']
        ];

        echo modificarUsuario($data);
        break;

    case 'DELETE':
        // 🔹 Eliminar usando usuario de sesión
        if (!isset($_SESSION['usuario'])) {
            echo json_encode(['success' => false, 'error' => 'No hay usuario en sesión']);
            exit();
        }

        $data = ['usuario_sesion' => $_SESSION['usuario']];
        echo eliminarUsuario($data);
        break;

    default:
        echo json_encode(['error' => 'Método no soportado']);
}
