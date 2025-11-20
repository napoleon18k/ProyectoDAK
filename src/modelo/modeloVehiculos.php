<?php

require_once __DIR__ . '/../conexion.php';

class Vehiculos
{
    private $conexion;

    public function __construct()
    {
        $this->conexion = conexion();
    }

  public function insertarVehiculos($marca, $modelo, $ano, $matricula, $autonomia, $tipo_conector, $id_duenio)
{
    // 🛑 QUITAR el código de debugging. Devolver un error si no hay sesión.
    if (!isset($_SESSION['id_usuario'])) {
        return false; // Retorna false si no hay ID de usuario logeado
    }
    
    $sql = "INSERT INTO vehiculo (marca, modelo, ano, matricula, autonomia, tipo_conector, duenio) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $this->conexion->prepare($sql);
    return $stmt->execute([$marca, $modelo, $ano, $matricula, $autonomia, $tipo_conector, $id_duenio]);
}

    public function eliminarVehiculos($id)
    {
        // 🛑 CORRECCIÓN: 'vehiculos' cambiado a 'vehiculo'
        $sql = "DELETE FROM vehiculo WHERE id = ?";
        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute([$id]);
    }

 public function modificarVehiculos($id, $nuevaAutonomia, $nuevoConector)
    {
        // 🛑 CORRECCIÓN: 'vehiculos' cambiado a 'vehiculo'
        $sql = "UPDATE vehiculo SET autonomia = ?, tipo_conector= ? WHERE id = ?";
        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute([$nuevaAutonomia, $nuevoConector, $id ]);
    }

function listarVehiculos() {
    // Listar solo los vehículos del dueño (no codificamos en JSON aquí)
    $pdo = conexion();
    // Intentamos obtener el dueño desde la sesión si no se pasa por parámetro
    if (session_status() === PHP_SESSION_NONE) {
        @session_start();
    }
    $id_duenio = $_SESSION['id_usuario'] ?? null;
    if (!$id_duenio) {
        return []; // Sin sesión, devolvemos lista vacía
    }
    $stmt = $pdo->prepare("SELECT * FROM vehiculo WHERE duenio = ?");
    $stmt->execute([$id_duenio]);
    $vehiculos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $vehiculos;
}

}