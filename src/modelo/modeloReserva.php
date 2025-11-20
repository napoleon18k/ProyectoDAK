<?php
// PROYECTO_DOCKER/src/modelo/modeloReserva.php

// Incluir el archivo de conexión (Ajusta esta ruta si es necesario)
// Desde /src/modelo/ hay que subir 2 niveles para llegar a conexion.php
require_once __DIR__ . '/../conexion.php'; 

class ReservaModelo {
    private $pdo;

    public function __construct() {
        // Obtener la conexión directamente en el constructor del Modelo
        try {
            $this->pdo = conexion();
            // Aseguramos que PDO esté en modo de excepción para capturar errores de DB
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
        } catch (\Exception $e) {
            // Manejar error de conexión fatal
            error_log("Error fatal al conectar a la DB desde el Modelo: " . $e->getMessage());
            throw new \Exception("Error al establecer la conexión con la base de datos.");
        }
    }

    /**
     * Devuelve el objeto PDO. Necesario para que el Controlador maneje la Transacción.
     * @return PDO
     */
    public function getPDO() {
        return $this->pdo;
    }

    /**
     * Verifica si un cargador está ocupado en un slot de tiempo específico.
     * @param int $cargadorId ID del cargador.
     * @param string $slot Fecha y hora de la reserva (formato YYYY-MM-DD HH:MM:SS).
     * @return bool True si está ocupado, False si está libre.
     */
   public function obtenerSlotsOcupados($cargadorId, $fecha = null) {
    $sql = "SELECT rc.slot_horario
            FROM reserva_cargador rc
            JOIN reserva r ON rc.id = r.Id_Reserva
            WHERE r.Id_Cargador = :idC
            AND rc.estado IN ('pendiente','activa')";
    
    // Si se filtra por fecha: YYYY-MM-DD
    if ($fecha) {
        $sql .= " AND DATE(rc.slot_horario) = :fecha";
    }

    $stmt = $this->pdo->prepare($sql);
    $stmt->bindValue(':idC', $cargadorId, PDO::PARAM_INT);

    if ($fecha)
        $stmt->bindValue(':fecha', $fecha);

    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

    /**
     * Intenta crear una nueva reserva, insertando registros en tres tablas.
     * @param int $clienteId ID del cliente.
     * @param int $cargadorId ID del cargador.
     * @param string $slot Fecha y hora de inicio de la reserva.
     * @param string $nombre Nombre de la reserva.
     * @param int $duracion Duración en minutos (default 60 min).
     * @return bool True en caso de éxito, False en caso de error.
     */
    public function crearReserva($clienteId, $cargadorId, $slot, $nombre, $duracion = 60) {
        // La transacción se maneja en el Controlador (usando getPDO()).
        
        try {
            // 1. INSERCIÓN EN reserva_cargador
            $sql1 = "INSERT INTO reserva_cargador (nombre, estado, slot_horario, duracion_minutos) 
                     VALUES (:nombre, 'activa', :slot_horario, :duracion_minutos)";
            
            $stmt1 = $this->pdo->prepare($sql1);
            $stmt1->bindParam(':nombre', $nombre);
            $stmt1->bindParam(':slot_horario', $slot);
            $stmt1->bindParam(':duracion_minutos', $duracion, PDO::PARAM_INT);
            $stmt1->execute();
            
            $idReserva = $this->pdo->lastInsertId();

            // 2. INSERCIÓN EN reserva (Relación Reserva_Cargador - Cargador)
            $sql2 = "INSERT INTO reserva (Id_Reserva, Id_Cargador) 
                     VALUES (:id_reserva, :id_cargador)";
            
            $stmt2 = $this->pdo->prepare($sql2);
            $stmt2->bindParam(':id_reserva', $idReserva, PDO::PARAM_INT);
            $stmt2->bindParam(':id_cargador', $cargadorId, PDO::PARAM_INT);
            $stmt2->execute();

            // 3. INSERCIÓN EN hacereserva (Relación Reserva - Usuario)
            $sql3 = "INSERT INTO hacereserva (id_R, id_U) 
                     VALUES (:id_reserva, :id_usuario)";
            
            $stmt3 = $this->pdo->prepare($sql3);
            $stmt3->bindParam(':id_reserva', $idReserva, PDO::PARAM_INT);
            $stmt3->bindParam(':id_usuario', $clienteId, PDO::PARAM_INT);
            $stmt3->execute();

            return true;
            
        } catch (\PDOException $e) {
            // El controlador manejará el rollback
            error_log("Error al crear reserva (multi-tabla): " . $e->getMessage());
            // Relanzar la excepción para que el controlador la capture y haga rollback
            throw $e; 
        }
    }
}
?>