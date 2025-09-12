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
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>GESTIÓN PUNTOS DE CARGA</title>
    <style>
        /* Reset básico */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: #fff;
            color: #000;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 40px 20px;
        }

        .contenedor {
            background: #f9f9f9;
            border: 1px solid #ccc;
            border-radius: 10px;
            padding: 30px 40px;
            width: 100%;
            max-width: 700px;
            text-align: center;
        }

        h2 {
            font-size: 1.8rem;
            margin-bottom: 20px;
            color: #000;
            border-bottom: 1px solid #ccc;
            padding-bottom: 6px;
        }

        form {
            margin-bottom: 30px;
            text-align: left;
        }

        input[type="text"],
        input[type="number"],
        select {
            width: 100%;
            padding: 10px 12px;
            margin: 10px 0 20px 0;
            border: 1px solid #ccc;
            border-radius: 6px;
            background-color: #fff;
            color: #000;
            font-size: 1rem;
        }

        input:focus,
        select:focus {
            outline: none;
            border-color: #000;
        }

        button {
            background: #000;
            border: none;
            color: #fff;
            padding: 10px 22px;
            font-size: 1rem;
            font-weight: 600;
            border-radius: 6px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        button:hover {
            background: #333;
        }

        ul#resultado {
            list-style: none;
            padding-left: 0;
            max-height: 260px;
            overflow-y: auto;
            border: 1px solid #ccc;
            border-radius: 6px;
            background-color: #fff;
            color: #000;
            font-size: 1rem;
            text-align: left;
        }

        ul#resultado li {
            padding: 10px 14px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        ul#resultado li:last-child {
            border-bottom: none;
        }
    </style>
</head>

<body>
    <div class="contenedor">
        <h2>Agregar Punto de Carga</h2>
        <form id="form-agregar">
            <input type="text" id="coordenadas" placeholder="Coordenadas" required>
            <input type="text" id="direccion" placeholder="Dirección" required>
            <input type="text" id="tipocargador" placeholder="Tipo de cargador" required>
            <input type="text" id="tarifa" placeholder="Tarifa" required>
            <select name="estado" id="estado">
                <option value="disponible">Disponible</option>
                <option value="inactivo">Inactivo</option>
                <option value="ocupado">Ocupado</option>
            </select>
            <button type="submit">Registrar</button>
        </form>

        <h2>Eliminar Punto de Carga</h2>
        <form id="form-eliminar">
            <input type="number" id="id-eliminar" placeholder="ID a eliminar" required>
            <button type="submit">Eliminar</button>
        </form>

        <h2>Modificar Punto de carga</h2>
        <form id="form-modificar">
            <input type="text" id="mod-id" placeholder="ID del Punto" required>
            <input type="text" id="mod-coordenadas" placeholder="Coordenadas" required>
            <input type="text" id="mod-direccion" placeholder="Dirección" required>
            <input type="text" id="mod-tarifa" placeholder="Tarifa" required>
            <input type="text" id="mod-tipocargador" placeholder="Tipo de cargador" required>
            <select name="mod-estado" id="mod-estado">
                <option value="disponible">Disponible</option>
                <option value="inactivo">Inactivo</option>
                <option value="ocupado">Ocupado</option>
            </select>
            <button type="submit">Modificar</button>
        </form>

        <h2>Lista de Puntos de Cargas</h2>
        <button id="btn-listar">Listar Puntos de Cargas</button>
        <ul id="resultado"></ul>

        <button onclick="location.href='administrador.php'">Volver</button>
    </div>
</body>

</html>
