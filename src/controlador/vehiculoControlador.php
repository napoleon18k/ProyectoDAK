<?php
require_once __DIR__ . '/../modelo/modeloVehiculos.php';
$vehiculoObj = new Vehiculos();

function insertarVehiculos() {
    global $vehiculoObj;
    $data = $_POST;
    $marca = $data['marca'] ?? '';
    $modelo = $data['modelo'] ?? '';
    $ano = $data['ano'] ?? '';
    $matricula = $data['matricula'] ?? '';
    $autonomia = $data['autonomia'] ?? '';
    $tipo_conector = $data['tipo_conector'] ?? '';

    //tomar el nombre del usuario de la variable $_session para agregarlo a la tabla de vehiculos
    return $vehiculoObj->insertarVehiculos($marca, $modelo, $ano, $matricula, $autonomia, $tipo_conector)
        ? json_encode(['success' => true])
        : json_encode(['success' => false]);
}

function eliminarVehiculos($data) {
    global $vehiculoObj;
    $id = $data['id'] ?? '';
    return $vehiculoObj->eliminarVehiculos($id)
        ? json_encode(['success' => true])
        : json_encode(['success' => false]);
}

function modificarVehiculos($data) {
    global $vehiculoObj;
    $id = $data['id'] ?? '';
    $nuevaAutonomia = $data['nuevaautonomia'] ?? '';
    $nuevoConector = $data['tipo_conector'] ?? '';
    return $vehiculoObj->modificarVehiculos($id, $nuevaAutonomia, $nuevoConector)
        ? json_encode(['success' => true])
        : json_encode(['success' => false]);
}

function listarVehiculos() {
    global $vehiculoObj;
    return json_encode($vehiculoObj->listarVehiculos());
}

?>