<?php
// models/ViajeModel.php

require_once dirname(__DIR__) . '/../conexion.php'; 

class ViajeModel {
    private $conn;
    private $id_usuario;

    public function __construct($id_usuario) {
        $this->conn = conexion();
        $this->id_usuario = $id_usuario;
    }

    // 💡 IMPORTANTE: Esta lógica asume que el front-end puede determinar el ID_Slot y el ID_Cargador
    // del mejor cargador disponible en la estación sugerida. Como esa lógica NO está en el JS,
    // he añadido un valor estático temporal (401 y 301) para que el código funcione.
    // DEBES IMPLEMENTAR la lógica para seleccionar un slot/cargador real.
    const TEMPORAL_ID_SLOT = 401; 
    const TEMPORAL_ID_CARGADOR = 301;

    /**
     * Guarda el viaje completo, incluyendo vehículo, paradas y reservas.
     * @param array $datos Los datos del viaje.
     * @return array Resultado del proceso.
     */
    public function guardarViajeCompleto($datos) {
        $id_vehiculo = intval($datos['id_vehiculo']);
        $estaciones = $datos['estaciones'];
        $origen = $datos['origen'];
        $destino = $datos['destino'];
        $fecha_viaje = date('Y-m-d');

        if (!$id_vehiculo || !is_array($estaciones)) {
            return ['success' => false, 'error' => 'Datos de viaje incompletos o inválidos.'];
        }

        $this->conn->beginTransaction();

        try {
            // --- INSERCIÓN DEL VIAJE (PASO 1) ---
            $sqlViaje = "INSERT INTO viaje (fecha_v, origen, destino) VALUES (?, ?, ?)";
            $stmtViaje = $this->conn->prepare($sqlViaje);
            $stmtViaje->execute([$fecha_viaje, $origen, $destino]);
            $id_viaje = $this->conn->lastInsertId();

            // --- VINCULAR VIAJE CON VEHÍCULO (sehace) (PASO 2) ---
            $sqlSeHace = "INSERT INTO sehace (id_VJE, id_V) VALUES (?, ?)";
            $stmtSeHace = $this->conn->prepare($sqlSeHace);
            $stmtSeHace->execute([$id_viaje, $id_vehiculo]);

            $ids_reservas_asociacion = [];
            
            // --- PROCESAR CADA ESTACIÓN COMO PARADA/RESERVA ---
            foreach ($estaciones as $est) {
                $id_estacion = intval($est['id_estacion']);
                $orden = intval($est['orden']);
                
                // --- INSERTAR EN reserva_cargador (Reserva Base) (PASO 3) ---
                $sqlReservaCargador = "INSERT INTO reserva_cargador (nombre, estado) VALUES (?, 'pendiente')";
                $stmtReservaCargador = $this->conn->prepare($sqlReservaCargador);
                $stmtReservaCargador->execute(["Reserva Estación $id_estacion - Viaje $id_viaje"]);
                $id_reserva_cargador = $this->conn->lastInsertId();

                // --- INSERTAR EN reserva (Asociación Slot/Cargador) (PASO 4) ---
                // Aquí deberías tener la lógica para elegir el cargador y el slot (TEMPORAL: 401, 301)
                $id_slot = self::TEMPORAL_ID_SLOT; 
                $id_cargador = self::TEMPORAL_ID_CARGADOR;
                
                $sqlReserva = "INSERT INTO reserva (ID_Reserva, ID_Slot, ID_Cargador) VALUES (?, ?, ?)";
                $stmtReserva = $this->conn->prepare($sqlReserva);
                $stmtReserva->execute([$id_reserva_cargador, $id_slot, $id_cargador]);
                $id_reserva_asociacion = $this->conn->lastInsertId();
                $ids_reservas_asociacion[] = $id_reserva_asociacion;

                // --- ASOCIAR RESERVA A USUARIO (hacereserva) (PASO 5) ---
                $sqlHacer = "INSERT INTO hacereserva (id_R, id_U) VALUES (?, ?)";
                $stmtHacer = $this->conn->prepare($sqlHacer);
                $stmtHacer->execute([$id_reserva_cargador, $this->id_usuario]);

                // --- INSERTAR PARADA (PASO 6) ---
                $sqlParada = "INSERT INTO parada (orden, id_V) VALUES (?, ?)";
                $stmtParada = $this->conn->prepare($sqlParada);
                $stmtParada->execute([$orden, $id_viaje]);
                $id_parada = $this->conn->lastInsertId();

                // --- VINCULAR PARADA CON RESERVA DE ASOCIACIÓN (mediante) (PASO 7) ---
                $sqlMediante = "INSERT INTO mediante (id_P, id_R) VALUES (?, ?)";
                $stmtMediante = $this->conn->prepare($sqlMediante);
                $stmtMediante->execute([$id_parada, $id_reserva_asociacion]);
            }

            $this->conn->commit();

            return [
                'success' => true,
                'id_viaje' => $id_viaje,
                'reservas' => $ids_reservas_asociacion,
                'message' => '¡Viaje, paradas y reservas guardados correctamente!'
            ];

        } catch (PDOException $e) {
            $this->conn->rollBack();
            // Controla el error de duplicado (si se intenta reservar el mismo slot/cargador)
            if ($e->getCode() === '23000' && strpos($e->getMessage(), 'UQ_Slot_Cargador') !== false) {
                 return ['success' => false, 'error' => 'El slot horario y cargador seleccionado ya está reservado.', 'detalle' => $e->getMessage()];
            }
            return ['success' => false, 'error' => 'Error en la base de datos', 'detalle' => $e->getMessage()];
        }
    }
}