<?php

require_once __DIR__ . '/../modelo/modeloReserva.php';

class ReservaControlador {
    private $modelo;

    public function __construct() {
        // El constructor del Modelo ya gestiona la conexión a la DB
        $this->modelo = new ReservaModelo();
    }

    /**
     * Procesa la creación de un lote de reservas desde el carrito.
     */
    public function crearReservasBatch() {
        // 1. Obtener ID del usuario logueado (Simulación)
        $clienteId = $this->obtenerIdClienteDeSesion(); 
        if (!$clienteId) {
            http_response_code(401);
            echo json_encode(['ok' => false, 'message' => 'Usuario no autenticado.']);
            return;
        }
        
        // 2. Leer la data del POST
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['reservas']) || !is_array($data['reservas'])) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'message' => 'Datos de reservas inválidos.']);
            return;
        }

        $reservasSolicitadas = $data['reservas'];
        $resultados = ['exito' => 0, 'fallo' => 0, 'detalles' => []];

        // 3. Iniciar Transacción (obtenemos PDO desde el Modelo)
        $pdo = $this->modelo->getPDO();
        $pdo->beginTransaction();

        try {
            foreach ($reservasSolicitadas as $reserva) {
                // Validación básica de campos requeridos
                if (!isset($reserva['cargadorId']) || !isset($reserva['slot']) || !is_numeric($reserva['cargadorId']) || (int)$reserva['cargadorId'] <= 0) {
                    $resultados['fallo']++;
                    $resultados['detalles'][] = ['status' => 'fallo', 'message' => 'ID de cargador o slot inválido.', 'data' => $reserva];
                    continue;
                }
                
                $cargadorId = (int)$reserva['cargadorId'];
                $slot = $reserva['slot']; 
                $nombre = "Carga en " . ($reserva['estacionNombre'] ?? 'Estación') . " - " . date('H:i', strtotime($slot));

                // 4. Verificar disponibilidad
                if ($this->modelo->obtenerSlotsOcupados($cargadorId, $slot)) {
                    $resultados['fallo']++;
                    $resultados['detalles'][] = ['status' => 'fallo', 'message' => 'Cargador ocupado en ese horario.', 'data' => $reserva];
                    continue;
                }

                // 5. Crear la reserva multi-tabla (puede lanzar una excepción PDO)
                if ($this->modelo->crearReserva($clienteId, $cargadorId, $slot, $nombre)) {
                    $resultados['exito']++;
                    $resultados['detalles'][] = ['status' => 'ok', 'message' => 'Reserva creada con éxito.', 'data' => $reserva];
                } else {
                    // Si el modelo devuelve false, pero no lanzó excepción (situación rara con transacciones)
                    $resultados['fallo']++;
                    $resultados['detalles'][] = ['status' => 'fallo', 'message' => 'Error indefinido al insertar reserva.', 'data' => $reserva];
                }
            }

            // Si llegamos aquí sin excepciones, confirmamos todas las inserciones.
            $pdo->commit();
            
            http_response_code(200);
            echo json_encode([
                'ok' => $resultados['fallo'] === 0, 
                'message' => "Procesadas {$resultados['exito']} reservas. Fallaron {$resultados['fallo']}.", 
                'resultados' => $resultados
            ]);

        } catch (\Exception $e) {
            $pdo->rollBack();
            http_response_code(500);
            echo json_encode(['ok' => false, 'message' => 'Error interno del servidor (Rollback aplicado): ' . $e->getMessage()]);
        }
    }

    /**
     * Simula la obtención del ID del cliente de la sesión.
     */
    private function obtenerIdClienteDeSesion() {
        // !!! REEMPLAZAR CON LA LÓGICA DE SESIÓN REAL !!!
        return 1; 
    }
}
?>