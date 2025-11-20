<?php
// controllers/ViajeController.php

require_once dirname(__DIR__) . '/../modelo/viajeModelo.php';

class ViajeController {
    private $modelo;

    public function __construct($id_usuario) {
        $this->modelo = new ViajeModel($id_usuario);
    }

    /**
     * Procesa la solicitud para guardar un nuevo viaje.
     * @param array $data Los datos del cuerpo de la solicitud (JSON).
     * @return array La respuesta para el cliente.
     */
    public function guardarViaje($data) {
        // Validación básica (el modelo hará la validación estricta de IDs)
        if (!isset($data['id_vehiculo']) || !isset($data['origen']) || !isset($data['destino'])) {
            http_response_code(400);
            return ['success' => false, 'error' => 'Faltan datos esenciales del viaje.'];
        }
        
        // Si no se necesitan paradas, el front-end puede enviar estaciones como un array vacío.
        if (!isset($data['estaciones'])) {
            $data['estaciones'] = [];
        }

        return $this->modelo->guardarViajeCompleto($data);
    }
}