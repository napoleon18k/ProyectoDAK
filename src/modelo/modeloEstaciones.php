<?php
require_once __DIR__ . '/../conexion.php';

class Estacion {
    private $conexion;

    public function __construct() {
        $this->conexion = conexion();
    }

    public function insertarEstacion($nombre, $direccion, $departamento, $lat, $lng, $cargadores) {
        $sql = "INSERT INTO estaciones (nombre, direccion, departamento, lat, lng, cargadores) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute([$nombre, $direccion, $departamento, $lat, $lng, json_encode($cargadores)]);
    }

    public function eliminarEstacion($id) {
        $sql = "DELETE FROM estaciones WHERE id = ?";
        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute([$id]);
    }

    public function modificarEstacion($id, $nombre, $direccion, $departamento, $lat, $lng, $cargadores) {
        $sql = "UPDATE estaciones 
                SET nombre = ?, direccion = ?, departamento = ?, lat = ?, lng = ?, cargadores = ? 
                WHERE id = ?";
        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute([$nombre, $direccion, $departamento, $lat, $lng, json_encode($cargadores), $id]);
    }

    public function listarEstaciones() {
        $stmt = $this->conexion->query("SELECT * FROM estaciones");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
