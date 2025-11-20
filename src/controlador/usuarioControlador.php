<?php
require_once __DIR__ . '/../modelo/usuario.php';

class UsuarioControlador {

    private $usuario;

    public function __construct() {
        $this->usuario = new Usuario();
    }

    public function insertarUsuario($usuario, $correo, $password, $rol) {

        // VALIDAR USUARIO
        if (strlen($usuario) < 4) {
            return ['success' => false, 'error' => 'El usuario debe tener al menos 4 caracteres'];
        }

        if (!preg_match('/^[a-zA-Z0-9_.-]+$/', $usuario)) {
            return ['success' => false, 'error' => 'El usuario solo puede contener letras, números y ._-'];
        }

        // VALIDAR CONTRASEÑA
        if (strlen($password) < 6) {
            return ['success' => false, 'error' => 'La contraseña debe tener al menos 6 caracteres'];
        }

        if (!preg_match('/[0-9]/', $password)) {
            return ['success' => false, 'error' => 'La contraseña debe contener al menos un número'];
        }

        if ($this->usuario->existeUsuario($usuario)) {
            return ['success' => false, 'error' => 'El usuario ya existe'];
        }

        $hashed = password_hash($password, PASSWORD_BCRYPT);

        return $this->usuario->insertar($usuario, $hashed, $rol, $correo)
            ? ['success' => true]
            : ['success' => false, 'error' => 'No se pudo insertar'];
    }

    public function listar() {
        return $this->usuario->listar();
    }

    // recibe ID
    public function eliminarUsuario($id_usuario) {
        if (empty($id_usuario) || !is_numeric($id_usuario)) {
            return ['success' => false, 'error' => 'ID inválido'];
        }

        return $this->usuario->eliminarPorId($id_usuario)
            ? ['success' => true]
            : ['success' => false, 'error' => 'El usuario no existe'];
    }

    public function modificar($id_usuario, $usuario, $correo, $password, $rol) {

        if (empty($id_usuario) || !is_numeric($id_usuario)) {
            return ['success' => false, 'error' => 'ID inválido'];
        }

        // VALIDAR USUARIO
        if (!empty($usuario)) {
            if (strlen($usuario) < 4) {
                return ['success' => false, 'error' => 'El usuario debe tener al menos 4 caracteres'];
            }

            if (!preg_match('/^[a-zA-Z0-9_.-]+$/', $usuario)) {
                return ['success' => false, 'error' => 'El usuario solo puede contener letras, números y ._-'];
            }
        }

        // VALIDAR CONTRASEÑA
        $passwordHashed = null;

        if (!empty($password)) {

            if (strlen($password) < 6) {
                return ['success' => false, 'error' => 'La contraseña debe tener al menos 6 caracteres'];
            }

            if (!preg_match('/[0-9]/', $password)) {
                return ['success' => false, 'error' => 'La contraseña debe contener al menos un número'];
            }

            $passwordHashed = password_hash($password, PASSWORD_BCRYPT);
        }

        return $this->usuario->modificarPorId(
            $id_usuario,
            $usuario,
            $correo,
            $passwordHashed,
            $rol
        )
            ? ['success' => true]
            : ['success' => false, 'error' => 'El usuario no existe o no hubo cambios'];
    }

    public function login($usuario, $password) {
        return $this->usuario->login($usuario, $password);
    }
}
