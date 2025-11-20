<?php
require_once __DIR__ . '/../conexion.php';

class EstacionModel {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Crear estación con cargadores
    public function crearEstacion($nombre, $direccion, $departamento, $lat, $lng, $cargadores) {
        try {
            $this->conn->beginTransaction();

            $stmt = $this->conn->prepare("INSERT INTO estacion (nombre, direccion, departamento, lat, lng) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$nombre, $direccion, $departamento, $lat, $lng]);
            $idEstacion = $this->conn->lastInsertId();

            $stmtCargador = $this->conn->prepare("INSERT INTO cargador (potencia, tipo, id_E, conectores, precio_kWh, precio_base) VALUES (?, ?, ?, ?, ?, ?)");
            foreach ($cargadores as $c) {
                $stmtCargador->execute([
                    $c['potencia'],
                    $c['tipo'],
                    $idEstacion,
                    $c['conectores'] ?? '1 (con cable)', // valor por defecto
                    $c['precio_kWh'] ?? 0,              // valor por defecto
                    $c['precio_base'] ?? 0              // valor por defecto
                ]);
            }

            $this->conn->commit();
            return ['success' => true, 'message' => 'Estación y cargadores guardados correctamente.'];
        } catch (Exception $e) {
            $this->conn->rollBack();
            return ['success' => false, 'error' => 'Error al guardar estación: ' . $e->getMessage()];
        }
    }

    // Obtener todas las estaciones con cargadores
    public function obtenerEstaciones() {
        $sql = "
            SELECT e.id, e.nombre, e.direccion, e.departamento, e.lat, e.lng,
                   GROUP_CONCAT(CONCAT(c.tipo, ' - ', c.potencia, ' kW') SEPARATOR '\n') AS cargadores_formateados
            FROM estacion e
            LEFT JOIN cargador c ON e.id = c.id_E
            GROUP BY e.id
        ";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Modificar estación y sus cargadores
    public function modificarEstacion($id, $nombre, $direccion, $departamento, $lat, $lng, $cargadores) {
        try {
            $this->conn->beginTransaction();

            // Actualizar estación
            $stmt = $this->conn->prepare("UPDATE estacion SET nombre=?, direccion=?, departamento=?, lat=?, lng=? WHERE id=?");
            $stmt->execute([$nombre, $direccion, $departamento, $lat, $lng, $id]);

            // Eliminar cargadores antiguos
            $stmtDel = $this->conn->prepare("DELETE FROM cargador WHERE id_E=?");
            $stmtDel->execute([$id]);

            // Insertar cargadores nuevos
            $stmtCargador = $this->conn->prepare("INSERT INTO cargador (potencia, tipo, id_E, conectores, precio_kWh, precio_base) VALUES (?, ?, ?, ?, ?, ?)");
            foreach ($cargadores as $c) {
                $stmtCargador->execute([
                    $c['potencia'],
                    $c['tipo'],
                    $id,
                    $c['conectores'] ?? '1 (con cable)',
                    $c['precio_kWh'] ?? 0,
                    $c['precio_base'] ?? 0
                ]);
            }

            $this->conn->commit();
            return ['success' => true, 'message' => 'Estación modificada correctamente'];
        } catch (Exception $e) {
            $this->conn->rollBack();
            return ['success' => false, 'error' => 'Error al modificar estación: ' . $e->getMessage()];
        }
    }

    // Eliminar estación y sus cargadores
    public function eliminarEstacion($id) {
        try {
            $this->conn->beginTransaction();

            $stmt1 = $this->conn->prepare("DELETE FROM cargador WHERE id_E = ?");
            $stmt1->execute([$id]);

            $stmt2 = $this->conn->prepare("DELETE FROM estacion WHERE id = ?");
            $stmt2->execute([$id]);

            $this->conn->commit();
            return ['success' => true, 'message' => 'Estación eliminada correctamente'];
        } catch (Exception $e) {
            $this->conn->rollBack();
            return ['success' => false, 'error' => 'Error al eliminar estación: ' . $e->getMessage()];
        }
    }
}
