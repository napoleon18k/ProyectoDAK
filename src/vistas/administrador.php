<?php
session_start();

// Verificamos si hay usuario logueado y rol admin
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
    // Redirigimos al login si no es admin
    header('Location: login.html');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menú Principal - Admin</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f0f0f0;
        }
        .menu {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        .menu button {
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            border: none;
            border-radius: 5px;
            background-color: #007BFF;
            color: white;
            transition: background-color 0.3s;
        }
        .menu button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="menu">
        <button onclick="location.href='gestionUsuariosadmin.php'">Gestión Usuarios</button>
        <button onclick="location.href='gestionCargasadmin.php'">Gestión Cargas</button>
        <button onclick="location.href='gestionVehiculosadmin.php'">Gestión Vehículos</button>
        <button id="btn-logout">Cerrar Sesión</button>
    </div>

    <script>
        document.getElementById('btn-logout').addEventListener('click', async () => {
            try {
                const res = await fetch('../api/logout.php', { method: 'POST' });
                const data = await res.json();
                if(data.success){
                    alert('Sesión cerrada correctamente.');
                    window.location.href = 'login.html';
                } else {
                    alert('Error al cerrar sesión.');
                }
            } catch (error) {
                console.error(error);
                alert('Error al conectar con el servidor.');
            }
        });
    </script>
</body>
</html>
