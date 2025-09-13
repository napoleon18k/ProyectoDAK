<?php
session_start();
require_once __DIR__ . '/../conexion.php';

class Vehiculos {
    private $conexion;

    public function __construct() {
        $this->conexion = conexion();
    }

    public function insertarVehiculos($marca, $modelo, $ano, $matricula, $autonomia, $tipo_conector) {
        if (!isset($_SESSION['id_usuario'])) {
            return false;
        }
        $id_duenio = $_SESSION['id_usuario'];

        $sql = "INSERT INTO vehiculos (marca, modelo, ano, matricula, autonomia, tipo_conector, duenio) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute([$marca, $modelo, $ano, $matricula, $autonomia, $tipo_conector, $id_duenio]);
    }

    public function eliminarVehiculos($id) {
        $sql = "DELETE FROM vehiculos WHERE id = ?";
        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute([$id]);
    }

    public function modificarVehiculos($id, $nuevaAutonomia, $nuevoConector) {
        $sql = "UPDATE vehiculos SET autonomia = ?, tipo_conector = ? WHERE id = ?";
        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute([$nuevaAutonomia, $nuevoConector, $id]);
    }

    public function listarVehiculos() {
        if (!isset($_SESSION['id_usuario'])) {
            return [];
        }
        $id_duenio = $_SESSION['id_usuario'];

        $sql = "SELECT * FROM vehiculos WHERE duenio = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([$id_duenio]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
