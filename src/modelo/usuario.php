<?php
require_once __DIR__ . '/../conexion.php';

class Usuario
{
    private $conexion;

    public function __construct()
    {
        $this->conexion = conexion();
    }

    public function insertar($usuario, $hashed, $rol, $correo)
    {
        $sql = "INSERT INTO usuario (usuario, password, rol, correo) VALUES (?, ?, ?, ?)";
        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute([$usuario, $hashed, $rol, $correo]);
    }

    public function eliminarPorId($id)
    {
        $sql = "DELETE FROM usuario WHERE id = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0; 
    }

    public function modificarPorId($id, $usuario, $correo, $passwordHashed, $rol)
    {
        $campos = [];
        $valores = [];

        if (!empty($usuario)) {
            $campos[] = "usuario = ?";
            $valores[] = $usuario;
        }

        if (!empty($correo)) {
            $campos[] = "correo = ?";
            $valores[] = $correo;
        }

        if (!empty($passwordHashed)) {
            $campos[] = "password = ?";
            $valores[] = $passwordHashed;
        }

        if (!empty($rol)) {
            $campos[] = "rol = ?";
            $valores[] = $rol;
        }

        if (empty($campos)) {
            return false;
        }

        $sql = "UPDATE usuario SET " . implode(", ", $campos) . " WHERE id = ?";
        $valores[] = $id;

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute($valores);

        return $stmt->rowCount() > 0; 
    }

    public function listar()
    {
        $stmt = $this->conexion->query("SELECT id, usuario, rol, correo FROM usuario");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }



public function login($usuario, $password)
{
   
    $sql = "SELECT id, password, rol, correo FROM usuario WHERE usuario = ?"; 
    $stmt = $this->conexion->prepare($sql);
    $stmt->execute([$usuario]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($data && password_verify($password, $data['password'])) {
        return [
            'success' => true,
            'rol' => $data['rol'],
            'id_usuario' => $data['id'],
            'correo' => $data['correo'] 
        ];
    }

    return ['success' => false];
}

    public function existeUsuario($usuario)
    {
        $sql = "SELECT COUNT(*) AS total FROM usuario WHERE usuario = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([$usuario]);
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'] > 0;
    }
}
