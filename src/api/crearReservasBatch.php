<?php
// PROYECTO_DOCKER/src/api/crearReservasBatch.php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); 
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'message' => 'Método no permitido.']);
    exit;
}

require_once __DIR__ . '/../controlador/reservaControlador.php';

try {
    
    // Crear el controlador (que internamente crea el Modelo y la conexión)
    $controlador = new ReservaControlador();
    $controlador->crearReservasBatch();
    
} catch (Exception $e) {
    // Manejo de errores de inicialización o si la conexión falla en el Modelo
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => 'Error al procesar la solicitud: ' . $e->getMessage()]);
}

?>