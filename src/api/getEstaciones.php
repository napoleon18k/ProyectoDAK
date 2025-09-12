<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../conexion.php'; 

try {
    // Conexión a la base de datos
    $conexion = conexion();

    $stmt = $conexion->query("SELECT nombre, direccion, departamento, lat, lng, cargadores FROM estaciones");
    $estaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Convertimos el JSON de cargadores a array y generamos un campo formateado
    foreach ($estaciones as &$est) {
        $cargadoresArray = json_decode($est['cargadores'], true);
        $est['cargadores'] = $cargadoresArray;

        // Crear un texto más legible
        $texto = '';
        foreach ($cargadoresArray as $c) {
            $texto .= $c['conectores'] . ' ' . $c['tipo'] . ' - ' . $c['potencia'];
            // agregamos "con cable" si no dice "sin cable"
            if (strpos(strtolower($c['conectores']), 'sin cable') === false) {
                $texto .= ' con cable';
            }
            $texto .= "\n";
        }
        $est['cargadores_formateados'] = trim($texto);
    }

    echo json_encode($estaciones);

} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
