<?php
require_once __DIR__ . '/../modelo/usuario.php'; // Ya está correcto, sigue dentro de src
$usuarioObj = new Usuario();

function insertarUsuario() {
    global $usuarioObj;
    $data = $_POST;
    $usuario = $data['usuario'] ?? '';
    $correo = $data['correo'] ?? '';
    $password = $data['password'] ?? '';
    $rol = $data['rol'] ?? '';

    // Verificamos si el usuario ya existe
    if ($usuarioObj->existeUsuario($usuario)) {
        return json_encode([
            'success' => false,
            'error' => 'El usuario ya existe'
        ]);
    }

    $hashed = password_hash($password, PASSWORD_BCRYPT);

    return $usuarioObj->insertar($usuario, $hashed, $rol, $correo)
        ? json_encode(['success' => true])
        : json_encode(['success' => false, 'error' => 'No se pudo insertar el usuario']);
}

function listarUsuarios() {
    global $usuarioObj;
    return json_encode($usuarioObj->listar());
}

// 🔹 Modificar usuario usando usuario de sesión
function modificarUsuario($data) {
    global $usuarioObj;

    $usuario_sesion = $data['usuario_sesion'] ?? '';
    $nuevoUsuario = $data['usuario'] ?? null;
    $nuevoCorreo = $data['correo'] ?? null;
    $nuevaPassword = $data['password'] ?? null;

    return $usuarioObj->modificarPorUsuario($usuario_sesion, $nuevoUsuario, $nuevoCorreo, $nuevaPassword)
        ? json_encode(['success' => true])
        : json_encode(['success' => false, 'error' => 'No se pudo modificar el usuario']);
}

// 🔹 Eliminar usuario usando usuario de sesión
function eliminarUsuario($data) {
    global $usuarioObj;

    $usuario_sesion = $data['usuario_sesion'] ?? '';

    return $usuarioObj->eliminarPorUsuario($usuario_sesion)
        ? json_encode(['success' => true])
        : json_encode(['success' => false, 'error' => 'No se pudo eliminar el usuario']);
}

function login() {
    global $usuarioObj;

    $usuario = $_POST["usuario"] ?? '';
    $password = $_POST['password'] ?? '';

    $result = $usuarioObj->login($usuario, $password);

    if ($result['success']) {
        return json_encode([
            'success' => true,
            'rol' => $result['rol']
        ]);
    } else {
        return json_encode([
            'success' => false,
            'error' => "Error al autenticar usuario. Verifique sus credenciales."
        ]);
    }
}
