<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../controlador/usuarioControlador.php';
session_start();


header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

$usuarioCtrl = new UsuarioControlador();
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    case 'OPTIONS':
        http_response_code(200);
        exit;

    case 'GET':
        if (isset($_GET['listar'])) {

            echo json_encode($usuarioCtrl->listar());
            exit;

        } else {
            echo json_encode(['error' => 'Acción GET no soportada']);
            exit;
        }
        break;


    case 'POST':

        // LOGIN
        if (isset($_POST['login'])) {

            $usuario = $_POST['usuario'] ?? '';
            $password = $_POST['password'] ?? '';

            $result = $usuarioCtrl->login($usuario, $password);

            if ($result['success']) {

                $_SESSION['usuario']    = $usuario;
                $_SESSION['rol']        = $result['rol'];
                $_SESSION['id_usuario'] = $result['id_usuario'];
                $_SESSION['correo']     = $result['correo'] ?? '';

                echo json_encode([
                    'success' => true,
                    'rol' => $result['rol'],
                    'id_usuario' => $result['id_usuario'],
                    'correo' => $result['correo'] ?? ''
                ]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Credenciales incorrectas']);
            }
            exit;
        }

        // INSERTAR USUARIO
        if (isset($_POST['insertar'])) {

            echo json_encode(
                $usuarioCtrl->insertarUsuario(
                    $_POST['usuario'] ?? '',
                    $_POST['correo'] ?? '',
                    $_POST['password'] ?? '',
                    $_POST['rol'] ?? ''
                )
            );
            exit;
        }

        echo json_encode(['error' => 'Acción POST no soportada']);
        exit;


    case 'PUT':

        parse_str(file_get_contents("php://input"), $_PUT);

        if (!isset($_SESSION['id_usuario'])) {
            echo json_encode(['success' => false, 'error' => 'No autorizado']);
            exit;
        }

        $id_sesion = $_SESSION['id_usuario'];
        $rol_sesion = $_SESSION['rol'];

        // Un cliente no puede modificar otros usuarios
        if ($rol_sesion == 'cliente' && (!isset($_SESSION['id_usuario']) || $id_sesion != $_SESSION['id_usuario'])) {
            echo json_encode(['success' => false, 'error' => 'No puedes modificar otros usuarios']);
            exit;
        }

        // Si es cliente su rol no puede cambiar
        $rol_final = $rol_sesion == 'cliente' ? 'cliente' : ($_PUT['rol'] ?? '');

        echo json_encode(
            $usuarioCtrl->modificar(
                $id_sesion,
                $_PUT['usuario'] ?? '',
                $_PUT['correo'] ?? '',
                $_PUT['password'] ?? '',
                $rol_final
            )
        );
        exit;


    case 'DELETE':

        parse_str(file_get_contents("php://input"), $_DELETE);

        if (!isset($_SESSION['rol'])) {
            echo json_encode(['success' => false, 'error' => 'No autorizado']);
            exit;
        }

        // ADMIN elimina a cualquiera
        if ($_SESSION['rol'] === 'admin') {

            if (!isset($_DELETE['id'])) {
                echo json_encode(['success' => false, 'error' => 'Falta parámetro id']);
                exit;
            }

            echo json_encode($usuarioCtrl->eliminarUsuario($_DELETE['id']));
            exit;
        }

        // CLIENTE elimina SOLO su cuenta
        if ($_SESSION['rol'] === 'cliente') {

            $id = $_SESSION['id_usuario'];

            echo json_encode($usuarioCtrl->eliminarUsuario($id));
            exit;
        }

        echo json_encode(['success' => false, 'error' => 'No autorizado']);
        exit;

} 
?>
