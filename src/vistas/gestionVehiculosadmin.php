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
  <title>GESTIÓN VEHÍCULOS</title>
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
      max-width: 650px;
      text-align: center;
    }

    h2 {
      font-size: 1.8rem;
      margin-bottom: 20px;
      color: #000;
      border-bottom: 1px solid #ccc;
      padding-bottom: 8px;
    }

    form {
      margin-bottom: 30px;
      text-align: left;
    }

    input[type="text"],
    input[type="number"],
    input[type="password"],
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
      padding: 10px 20px;
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

    ul#resultado button {
      background: #d9534f;
      color: #fff;
      border: none;
      border-radius: 6px;
      padding: 5px 12px;
      font-size: 0.9rem;
      cursor: pointer;
      transition: background 0.3s ease;
    }

    ul#resultado button:hover {
      background: #b52b27;
    }
  </style>
</head>

<body>
  <div class="contenedor">
    <h2>Agregar Vehículo</h2>
    <form id="form-agregar">
      <input type="text" id="marca" placeholder="Marca" required />
      <input type="text" id="modelo" placeholder="Modelo" required />
      <input type="text" id="ano" placeholder="Año" required />
      <input type="text" id="matricula" placeholder="Matrícula" required />
      <input type="text" id="autonomia" placeholder="Autonomía" required />
      <select name="tipo_conector" id="tipoconector">
        <option value="lenta">Lenta</option>
        <option value="rapida">Rápida</option>
        <option value="ultra">Ultra</option>
      </select>
      <button type="submit">Registrar</button>
    </form>

    <h2>Eliminar Vehículo</h2>
    <form id="form-eliminar">
      <input type="number" id="id-eliminar" placeholder="ID a eliminar" required />
      <button type="submit">Eliminar</button>
    </form>

    <h2>Modificar Vehículo</h2>
    <form id="form-modificar">
      <input type="number" id="id-modificar" placeholder="ID de vehículo" required />
      <input type="password" id="nueva-autonomia" placeholder="Nueva autonomía" required />
      <select name="tipo__conector" id="tipo__conector">
        <option value="lenta">Lenta</option>
        <option value="rapida">Rápida</option>
        <option value="ultra">Ultra</option>
      </select>
      <button type="submit">Modificar</button>
    </form>

    <h2>Lista de Vehículos</h2>
    <button id="btn-listar">Listar Vehículos</button>
    <ul id="resultado"></ul>

    <button onclick="location.href='administrador.php'">Volver</button>
  </div>
</body>

</html>
