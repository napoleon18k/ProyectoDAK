<?php
require_once __DIR__ . '/../conexion.php';

class Carga
{
    private $conexion;

    public function __construct()
    {
        $this->conexion = conexion();
    }

   public function insertarCarga($coordenadas, $direccion, $tarifa, $tipocargador, $estado)
{
    $sql = "INSERT INTO cargas (coordenadas, direccion, tarifa, tipocargador, estado) VALUES (?, ?, ?, ?, ?)";
    $stmt = $this->conexion->prepare($sql);
    return $stmt->execute([$coordenadas, $direccion, $tarifa, $tipocargador, $estado]);
}

  public function eliminarCarga($id)
    {
        $sql = "DELETE FROM cargas WHERE id = ?";
        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute([$id]);
    }


 public function modificarCarga($id, $coordenadas, $direccion, $tarifa, $tipocargador, $estado)
    {
        $sql = "UPDATE cargas SET coordenadas = ?, direccion= ?, tarifa= ?, tipocargador= ?, estado= ? WHERE id = ?";
        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute([$coordenadas, $direccion, $tarifa, $tipocargador, $estado, $id]);
    }

 public function listarCarga() {
        $stmt = $this->conexion->query("SELECT * FROM cargas");
        return $stmt->fetchAll(PDO::FETCH_ASSOC); // devolvemos array asociativo
        }


}
