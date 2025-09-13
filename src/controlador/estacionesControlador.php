<?php
require_once __DIR__ . '/../modelo/modeloEstaciones.php';
$estacionObj = new Estacion();

function insertarEstaciones() {
    global $estacionObj;
    $data = $_POST;

    $nombre = $data['nombre'] ?? '';
    $direccion = $data['direccion'] ?? '';
    $departamento = $data['departamento'] ?? '';
    $lat = $data['lat'] ?? '';
    $lng = $data['lng'] ?? '';
    $cargadores = $data['cargadores'] ?? [];

    return $estacionObj->insertarEstacion($nombre, $direccion, $departamento, $lat, $lng, $cargadores)
        ? json_encode(['success' => true])
        : json_encode(['success' => false]);
}

function eliminarEstaciones($data) {
    global $estacionObj;
    $id = $data['id'] ?? '';
    return $estacionObj->eliminarEstacion($id)
        ? json_encode(['success' => true])
        : json_encode(['success' => false]);
}

function modificarEstaciones($data) {
    global $estacionObj;
    $id = $data['id'] ?? '';
    $nombre = $data['nombre'] ?? '';
    $direccion = $data['direccion'] ?? '';
    $departamento = $data['departamento'] ?? '';
    $lat = $data['lat'] ?? '';
    $lng = $data['lng'] ?? '';
    $cargadores = $data['cargadores'] ?? [];

    return $estacionObj->modificarEstacion($id, $nombre, $direccion, $departamento, $lat, $lng, $cargadores)
        ? json_encode(['success' => true])
        : json_encode(['success' => false]);
}

function listarEstaciones() {
    global $estacionObj;
    return json_encode($estacionObj->listarEstaciones());
}
