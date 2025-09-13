<?php
session_start(); 
// Verificamos si hay usuario logueado y rol cliente
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'cliente') {
    header('Location: login.html');
    exit();
}

require_once '../conexion.php'; 
$conn = conexion();

$id_usuario = $_SESSION['id_usuario'];

// 📌 Traemos viajes del usuario logueado
$sql = "SELECT v.id_viaje, v.id_vehiculo, v.fecha_viaje, v.origen, v.destino
        FROM viajes v
        WHERE v.id_usuario = ?
        ORDER BY v.fecha_viaje DESC";
$stmt = $conn->prepare($sql);
$stmt->execute([$id_usuario]);
$viajes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 📌 Traemos paradas asociadas a cada viaje
$paradasPorViaje = [];
if ($viajes) {
    $ids = implode(',', array_column($viajes, 'id_viaje'));
    $sqlParadas = "SELECT p.id_viaje, p.orden, e.nombre AS estacion, e.departamento
                   FROM paradas p
                   INNER JOIN reservas r ON p.id_reserva = r.id_reserva
                   INNER JOIN estaciones e ON r.id_estacion = e.id
                   WHERE p.id_viaje IN ($ids)
                   ORDER BY p.id_viaje, p.orden ASC";
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
                    <td><?= htmlspecialchars($v['fecha_viaje']) ?></td>
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
