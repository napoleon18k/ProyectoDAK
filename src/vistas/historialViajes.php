<?php
session_start(); 
// Inicia la sesión para poder acceder a variables de sesión guardadas

// Verificamos si hay usuario logueado y si su rol es "cliente"
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'cliente') {
    // Si no existe usuario en sesión o el rol no es cliente, lo redirige al login
    header('Location: login.html');
    exit(); // Detiene la ejecución del script
}
?>


<?php
include '../conexion.php'; 

// Crea la conexión usando la función definida en conexion.php
$conn = conexion();

// Consulta SQL: obtiene todos los viajes, ordenados por fecha de inicio descendente
$stmt = $conn->query("SELECT id_viaje, id_vehiculo, fecha_inicio FROM viaje ORDER BY fecha_inicio DESC");

// Almacena los resultados en un array asociativo
$viajes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<!-- Hace que la página se adapte a pantallas de celulares -->
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Historial de Viajes</title>

<!-- CSS principal de cliente -->
<link rel="stylesheet" href="../assets/css/cliente.css">

<!-- Estilos propios de esta página -->
<style>
    table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
    th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
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
</style>
</head>
<body>
<h2>Historial de Viajes</h2>

<!-- Tabla donde se muestran los viajes -->
<table>
    <thead>
        <tr>
            <th>ID Viaje</th>
            <th>ID Vehículo</th>
            <th>Fecha Inicio</th>
        </tr>
    </thead>
    <tbody>
        <!-- Si hay registros de viajes -->
        <?php if(count($viajes) > 0): ?>
            <!-- Recorre cada viaje y muestra sus datos en la tabla -->
            <?php foreach($viajes as $row): ?>
                <tr>
                    <!-- htmlspecialchars evita inyección de código HTML -->
                    <td><?= htmlspecialchars($row['id_viaje'] ?? '') ?></td>
                    <td><?= htmlspecialchars($row['id_vehiculo'] ?? '') ?></td>
                    <td><?= htmlspecialchars($row['fecha_inicio'] ?? '') ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <!-- Si no hay viajes muestra un mensaje -->
            <tr><td colspan="3">No hay viajes registrados</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<!-- Botón para volver a la página principal -->
<button class="btn-volver" onclick="window.location.href='PrincipalCliente.html'">Volver a Principal</button>

</body>
</html>
