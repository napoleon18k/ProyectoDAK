<?php
session_start();
// Verificamos si hay usuario logueado y rol admin
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
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
* {margin:0;padding:0;box-sizing:border-box;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;}
body {background:#fff;color:#000;min-height:100vh;display:flex;justify-content:center;align-items:flex-start;padding:40px 20px;}
.contenedor {background:#f9f9f9;border:1px solid #ccc;border-radius:10px;padding:30px 40px;width:100%;max-width:700px;text-align:center;}
h2 {font-size:1.8rem;margin-bottom:20px;color:#000;border-bottom:1px solid #ccc;padding-bottom:6px;}
form {margin-bottom:30px;text-align:left;}
input[type="text"],input[type="number"],select {width:100%;padding:10px 12px;margin:10px 0 20px 0;border:1px solid #ccc;border-radius:6px;background-color:#fff;color:#000;font-size:1rem;}
input:focus,select:focus {outline:none;border-color:#000;}
button {background:#000;border:none;color:#fff;padding:10px 22px;font-size:1rem;font-weight:600;border-radius:6px;cursor:pointer;transition:background 0.3s ease;}
button:hover {background:#333;}
ul#resultado {list-style:none;padding-left:0;max-height:260px;overflow-y:auto;border:1px solid #ccc;border-radius:6px;background-color:#fff;color:#000;font-size:1rem;text-align:left;}
ul#resultado li {padding:10px 14px;border-bottom:1px solid #eee;display:flex;justify-content:space-between;align-items:center;}
ul#resultado li:last-child {border-bottom:none;}
.cargador-group {display:flex;gap:10px;align-items:center;margin-bottom:10px;}
.cargador-group input,.cargador-group select {flex:1;}
.cargador-group button {flex:0 0 35px;background:#c00;color:#fff;border:none;border-radius:4px;cursor:pointer;}
.cargador-group button:hover {background:#900;}
</style>
</head>
<body>

<div class="contenedor">

<h2>Agregar Punto de Carga</h2>
<form id="form-agregar">
  <input type="text" id="nombre" placeholder="Nombre" required>
  <input type="text" id="direccion" placeholder="Dirección" required>
  <input type="text" id="departamento" placeholder="Departamento" required>
  <input type="text" id="lat" placeholder="Latitud">
  <input type="text" id="lng" placeholder="Longitud">

  <h3>Cargadores</h3>
  <div id="cargadoresContainer"></div>
  <button type="button" id="btnAgregarCargador">+ Agregar cargador</button>

  <br><br>
  <button type="submit">Registrar</button>
</form>

<h2>Eliminar Punto de Carga</h2>
<form id="form-eliminar">
  <input type="number" id="id-eliminar" placeholder="ID a eliminar" required>
  <button type="submit">Eliminar</button>
</form>

<h2>Modificar Punto de carga</h2>
<form id="form-modificar">
  <input type="number" id="mod-id" placeholder="ID del Punto" required>
  <input type="text" id="mod-nombre" placeholder="Nombre" required>
  <input type="text" id="mod-direccion" placeholder="Dirección" required>
  <input type="text" id="mod-departamento" placeholder="Departamento" required>
  <input type="text" id="mod-lat" placeholder="Latitud">
  <input type="text" id="mod-lng" placeholder="Longitud">

  <h3>Cargadores</h3>
  <div id="modCargadoresContainer"></div>
  <button type="button" id="btnAgregarCargadorMod">+ Agregar cargador</button>

  <br><br>
  <button type="submit">Modificar</button>
</form>

<h2>Lista de Estaciones</h2>
<button id="btn-listar">Listar Estaciones</button>
<ul id="resultado"></ul>

<button onclick="location.href='administrador.php'">Volver</button>

</div>

<script>
const API_URL = "/src/api/apiCargas.php";

// 🔹 Crear un bloque de cargador
function crearCargador(containerId) {
  const container = document.getElementById(containerId);
  const div = document.createElement("div");
  div.className = "cargador-group";
  div.innerHTML = `
    <input type="number" class="potencia" placeholder="Potencia (kW)" min="1" required>
    <select class="tipo">
      <option value="Tipo2">Tipo2</option>
      <option value="CCS2">CCS2</option>
    </select>
    <input type="number" class="conectores" placeholder="Conectores" min="1">
    <input type="number" class="precio_kWh" placeholder="Precio kWh" step="0.01" min="0">
    <input type="number" class="precio_base" placeholder="Precio base" step="0.01" min="0">
    <button type="button" class="btnEliminar">X</button>
  `;
  container.appendChild(div);
  div.querySelector(".btnEliminar").addEventListener("click", () => div.remove());
}

// Agregar primer cargador al cargar
crearCargador("cargadoresContainer");
document.getElementById("btnAgregarCargador").addEventListener("click", () => crearCargador("cargadoresContainer"));
document.getElementById("btnAgregarCargadorMod").addEventListener("click", () => crearCargador("modCargadoresContainer"));

// 🔹 Agregar estación
document.getElementById("form-agregar").addEventListener("submit", e => {
  e.preventDefault();

  const nombre = document.getElementById("nombre").value.trim();
  const direccion = document.getElementById("direccion").value.trim();
  const departamento = document.getElementById("departamento").value.trim();
  const lat = document.getElementById("lat").value.trim();
  const lng = document.getElementById("lng").value.trim();

  const cargadores = [];
  document.querySelectorAll("#cargadoresContainer .cargador-group").forEach(c => {
    const potencia = parseFloat(c.querySelector(".potencia").value);
    const tipo = c.querySelector(".tipo").value;
    const conectores = parseInt(c.querySelector(".conectores").value) || null;
    const precio_kWh = parseFloat(c.querySelector(".precio_kWh").value) || null;
    const precio_base = parseFloat(c.querySelector(".precio_base").value) || null;

    if (potencia && tipo) {
      cargadores.push({ potencia, tipo, conectores, precio_kWh, precio_base });
    }
  });

  if (cargadores.length === 0) {
    alert("Agregue al menos un cargador válido.");
    return;
  }

  fetch(API_URL, {
    method: "POST",
    headers: {"Content-Type": "application/json"},
    body: JSON.stringify({ nombre, direccion, departamento, lat, lng, cargadores })
  })
  .then(r => r.json())
  .then(res => alert(res.success ? "Estación agregada" : "Error: " + res.error));
});

// 🔹 Eliminar estación
document.getElementById("form-eliminar").addEventListener("submit", e => {
  e.preventDefault();
  const id = document.getElementById("id-eliminar").value;
  fetch(API_URL, { method: "DELETE", body: `id=${id}` })
    .then(r => r.json())
    .then(res => alert(res.success ? "Estación eliminada" : "Error al eliminar"));
});

// 🔹 Modificar estación
document.getElementById("form-modificar").addEventListener("submit", e => {
  e.preventDefault();

  const id = document.getElementById("mod-id").value;
  const nombre = document.getElementById("mod-nombre").value.trim();
  const direccion = document.getElementById("mod-direccion").value.trim();
  const departamento = document.getElementById("mod-departamento").value.trim();
  const lat = document.getElementById("mod-lat").value.trim();
  const lng = document.getElementById("mod-lng").value.trim();

  const cargadores = [];
  document.querySelectorAll("#modCargadoresContainer .cargador-group").forEach(c => {
    const potencia = parseFloat(c.querySelector(".potencia").value);
    const tipo = c.querySelector(".tipo").value;
    const conectores = parseInt(c.querySelector(".conectores").value) || null;
    const precio_kWh = parseFloat(c.querySelector(".precio_kWh").value) || null;
    const precio_base = parseFloat(c.querySelector(".precio_base").value) || null;

    if (potencia && tipo) {
      cargadores.push({ potencia, tipo, conectores, precio_kWh, precio_base });
    }
  });

  if (cargadores.length === 0) {
    alert("Agregue al menos un cargador válido.");
    return;
  }

  fetch(API_URL, {
    method: "PUT",
    headers: {"Content-Type": "application/json"},
    body: JSON.stringify({ id, nombre, direccion, departamento, lat, lng, cargadores })
  })
  .then(r => r.json())
  .then(res => alert(res.success ? "Estación modificada" : "Error: " + res.error));
});

// 🔹 Listar estaciones
document.getElementById("btn-listar").addEventListener("click", () => {
  fetch(API_URL + "?listar=1")
    .then(r => r.json())
    .then(data => {
      const ul = document.getElementById("resultado");
      ul.innerHTML = "";
      data.forEach(estacion => {
        const li = document.createElement("li");
        li.textContent = `${estacion.id} - ${estacion.nombre} (${estacion.departamento})`;
        ul.appendChild(li);
      });
    });
});
</script>

</body>
</html>
