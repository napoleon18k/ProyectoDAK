<?php
require_once __DIR__ . '/../modelo/modeloEstaciones.php';

class EstacionController {
    private $model;

    public function __construct($conn) {
        $this->model = new EstacionModel($conn);
    }

    public function crearEstacion($data) {
        if (!isset($data['nombre'], $data['direccion'], $data['departamento'], $data['lat'], $data['lng'], $data['cargadores'])) {
            return ['success' => false, 'error' => 'Datos incompletos'];
        }
        return $this->model->crearEstacion(
            $data['nombre'],
            $data['direccion'],
            $data['departamento'],
            $data['lat'],
            $data['lng'],
            $data['cargadores']
        );
    }

    public function modificarEstacion($data) {
        if (empty($data['id'])) {
            return ['success' => false, 'error' => 'ID faltante'];
        }
        return $this->model->modificarEstacion(
            $data['id'],
            $data['nombre'],
            $data['direccion'],
            $data['departamento'],
            $data['lat'],
            $data['lng'],
            $data['cargadores']
        );
    }

    public function eliminarEstacion($data) {
        if (empty($data['id'])) {
            return ['success' => false, 'error' => 'ID faltante'];
        }
        return $this->model->eliminarEstacion($data['id']);
    }

    public function obtenerEstaciones() {
        return $this->model->obtenerEstaciones();
    }
}
