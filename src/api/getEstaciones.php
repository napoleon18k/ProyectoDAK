<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../conexion.php';

try {
    $conexion = conexion();

    // ✔ Esta consulta coincide con tu tabla 'estacion'
    $stmt = $conexion->query("SELECT id, nombre, direccion, departamento, lat, lng FROM estacion");
    $estaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ✔ Esta consulta coincide con lo mínimo que tu tabla 'cargador' debería tener
    //   Quito conectores, precio_kWh, precio_base porque NO existen en tu BD
    $stmtCargadores = $conexion->query("SELECT id, potencia, tipo, id_E FROM cargador");
    $cargadores = $stmtCargadores->fetchAll(PDO::FETCH_ASSOC);

    // Agrupamos los cargadores por estación
    $porEstacion = [];
    foreach ($cargadores as $c) {
        $porEstacion[$c['id_E']][] = $c;
    }

    // Unimos estaciones + sus cargadores
    foreach ($estaciones as &$est) {
        $idE = $est['id'];
        $est['cargadores'] = $porEstacion[$idE] ?? [];

        // Texto formateado para mostrar en el mapa
        $texto = '';
        foreach ($est['cargadores'] as $c) {
            $texto .= "{$c['tipo']} ({$c['potencia']} kW)\n";
        }
        $est['cargadores_formateados'] = trim($texto);
    }

    echo json_encode($estaciones, JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
