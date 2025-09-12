<?php
require_once __DIR__ . '/../conexion.php';

class Usuario
{
    private $conexion;

    public function __construct()
    {
        $this->conexion = conexion();
    }

    // Insertar usuario con password hasheado
    public function insertar($usuario, $hashed, $rol, $correo)
    {
        $sql = "INSERT INTO usuarios (usuario, password, rol, correo) VALUES (?, ?, ?, ?)";
        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute([$usuario, $hashed, $rol, $correo]);
    }

    // 🔹 Eliminar usuario por nombre de usuario
    public function eliminarPorUsuario($usuario)
    {
        $sql = "DELETE FROM usuarios WHERE usuario = ?";
        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute([$usuario]);
    }

    // 🔹 Modificar usuario por nombre de usuario
    public function modificarPorUsuario($usuarioSesion, $nuevoUsuario = null, $nuevoCorreo = null, $nuevaPassword = null)
    {
        $campos = [];
        $valores = [];

        if (!empty($nuevoUsuario)) {
            $campos[] = "usuario = ?";
            $valores[] = $nuevoUsuario;
        }

        if (!empty($nuevoCorreo)) {
            $campos[] = "correo = ?";
            $valores[] = $nuevoCorreo;
        }

        if (!empty($nuevaPassword)) {
            $hashed = password_hash($nuevaPassword, PASSWORD_BCRYPT);
            $campos[] = "password = ?";
            $valores[] = $hashed;
        }

        if (empty($campos)) {
            return false; // No hay nada que actualizar
        }

        $sql = "UPDATE usuarios SET " . implode(", ", $campos) . " WHERE usuario = ?";
        $valores[] = $usuarioSesion;

        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute($valores);
    }

    // Listar todos los usuarios
    public function listar()
    {
        $stmt = $this->conexion->query("SELECT id, usuario, rol, correo FROM usuarios");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Login seguro
    public function login($usuario, $password)
    {
        $sql = "SELECT id, password, rol FROM usuarios WHERE usuario = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([$usuario]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($resultado && password_verify($password, $resultado['password'])) {
            return [
                'success' => true,
                'rol' => $resultado['rol'],
                'id_usuario' => $resultado['id']
            ];
        }

        return ['success' => false];
    }

    // Verificar si existe un usuario
    public function existeUsuario($usuario)
    {
        $sql = "SELECT COUNT(*) as total FROM usuarios WHERE usuario = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([$usuario]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'] > 0;
    }
}
