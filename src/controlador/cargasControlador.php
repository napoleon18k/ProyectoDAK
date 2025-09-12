<?php
require_once __DIR__ . '/../modelo/modeloCargas.php';
$cargaObj = new Carga();

function insertarCargas() {
    error_log("Estado recibido: " . $_POST['estado']);
    global $cargaObj;
    $data = $_POST;
    $coordenadas = $data['coordenadas'] ?? '';
    $direccion = $data['direccion'] ?? '';
    $tarifa = $data['tarifa'] ?? '';
    $tipocargador = $data['tipocargador'] ?? '';
    $estado = $data['estado'] ?? '';
    return $cargaObj->insertarCarga($coordenadas, $direccion, $tarifa, $tipocargador, $estado)
        ? json_encode(['success' => true])
        : json_encode(['success' => false]);
}


function eliminarCargas($data) {
    global $cargaObj;
    $id = $data['id'] ?? '';
    return $cargaObj->eliminarCarga($id)
        ? json_encode(['success' => true])
        : json_encode(['success' => false]);
}
function modificarCargas($data) {
    global $cargaObj;
    $id = $data['id'] ?? '';
    $coordenadas = $data['coordenadas'] ?? '';
    $direccion = $data['direccion'] ?? '';
    $tarifa = $data['tarifa'] ?? '';
    $tipocargador = $data['tipocargador'] ?? '';
    $estado = $data['estado'] ?? '';
    return $cargaObj->modificarCarga($id, $coordenadas, $direccion, $tarifa, $tipocargador, $estado)
        ? json_encode(['success' => true])
        : json_encode(['success' => false]);
        
}


function listarCargas() {
    global $cargaObj;
    return json_encode($cargaObj->listarCarga());
}









?>