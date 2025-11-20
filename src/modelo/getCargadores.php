<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../conexion.php'; 

if (!isset($_GET['estacionId'])) {
    echo json_encode(['ok'=>false,'message'=>'Falta estacionId']);
    exit;
}

$estacionId = intval($_GET['estacionId']);

try {
    $conn = conexion();
    $stmt = $conn->prepare("SELECT id, tipo, potencia FROM cargador WHERE id_E = ?");
    $stmt->execute([$estacionId]);
    $cargadores = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['ok'=>true,'cargadores'=>$cargadores]);
} catch(PDOException $e) {
    echo json_encode(['ok'=>false,'message'=>$e->getMessage()]);
}
