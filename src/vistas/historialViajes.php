<?php
session_start(); 
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'cliente') {
    header('Location: login.html');
    exit();
}

require_once __DIR__ . '/../conexion.php'; 
$conn = conexion();

$id_usuario = $_SESSION['id_usuario'];

// 📌 Traemos viajes del usuario logueado
$sql = "SELECT v.id AS id_viaje, m.id AS id_vehiculo, v.fecha_v, v.origen, v.destino
FROM viaje v
INNER JOIN sehace s ON s.id_VJE = v.id
INNER JOIN vehiculo m ON m.id = s.id_V
WHERE m.duenio = ?
ORDER BY v.fecha_v DESC";
$stmt = $conn->prepare($sql);
$stmt->execute([$id_usuario]);
$viajes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 📌 Traemos paradas asociadas a cada viaje
$paradasPorViaje = [];
if ($viajes) {
    $ids = implode(',', array_column($viajes, 'id_viaje'));
    $sqlParadas = "SELECT p.id_V AS id_viaje, p.orden, e.nombre AS estacion, e.departamento
                   FROM parada p
                   INNER JOIN mediante m ON p.id = m.id_P
                   INNER JOIN guardareserva gr ON m.id_R = gr.id_R
                   INNER JOIN reserva r ON gr.id_R = r.id
                   INNER JOIN estacion e ON gr.id_E = e.id
                   ORDER BY p.id_V, p.orden ASC";
    $resParadas = $conn->query($sqlParadas)->fetchAll(PDO::FETCH_ASSOC);

    foreach ($resParadas as $p) {
        $paradasPorViaje[$p['id_viaje']][] = $p;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Historial de Viajes</title>
<link rel="stylesheet" href="../assets/css/cliente.css">
<style>
    table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
    th, td { border: 1px solid #ccc; padding: 8px; text-align: left; vertical-align: top; }
    th { background-color: #f2f2f2; }
    .btn-volver { 
        padding: 10px 20px; 
        background-color: #007bff; 
        color: #fff; 
        border: none; 
        cursor: pointer; 
        border-radius: 5px; 
    }
    .btn-volver:hover { background-color: #0056b3; }
    ul { margin: 0; padding-left: 20px; }
</style>
</head>
<body>
<h2>Historial de Viajes</h2>

<table>
    <thead>
        <tr>
            <th>ID Viaje</th>
            <th>Vehículo</th>
            <th>Fecha</th>
            <th>Origen</th>
            <th>Destino</th>
            <th>Paradas</th>
        </tr>
    </thead>
    <tbody>
        <?php if($viajes && count($viajes) > 0): ?>
            <?php foreach($viajes as $v): ?>
                <tr>
                    <td><?= htmlspecialchars($v['id_viaje']) ?></td>
                    <td><?= htmlspecialchars($v['id_vehiculo']) ?></td>
                    <td><?= htmlspecialchars($v['fecha_v']) ?></td>
                    <td><?= htmlspecialchars($v['origen']) ?></td>
                    <td><?= htmlspecialchars($v['destino']) ?></td>
                    <td>
                        <?php if(isset($paradasPorViaje[$v['id_viaje']])): ?>
                            <ul>
                                <?php foreach($paradasPorViaje[$v['id_viaje']] as $p): ?>
                                    <li><?= htmlspecialchars($p['orden']) ?>. 
                                        <?= htmlspecialchars($p['estacion']) ?> (<?= htmlspecialchars($p['departamento']) ?>)
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            Sin paradas
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="6">No hay viajes registrados</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<button class="btn-volver" onclick="window.location.href='PrincipalCliente.html'">Volver a Principal</button>

</body>
</html>
