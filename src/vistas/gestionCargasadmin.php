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
    /* mismo CSS que ya tenías */
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
      <input type="text" id="cargadores" placeholder='Cargadores (JSON ej: {"tipo":"rapido"})' required>
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
      <input type="text" id="mod-cargadores" placeholder='Cargadores (JSON)' required>
      <button type="submit">Modificar</button>
    </form>

    <h2>Lista de Estaciones</h2>
    <button id="btn-listar">Listar Estaciones</button>
    <ul id="resultado"></ul>

    <button onclick="location.href='administrador.php'">Volver</button>
  </div>

<script>
const API_URL = "/src/api/apiCargas.php"; 

// Agregar estación
document.getElementById("form-agregar").addEventListener("submit", e => {
  e.preventDefault();
  const data = new URLSearchParams();
  data.append("insertar", 1);
  data.append("nombre", document.getElementById("nombre").value);
  data.append("direccion", document.getElementById("direccion").value);
  data.append("departamento", document.getElementById("departamento").value);
  data.append("lat", document.getElementById("lat").value);
  data.append("lng", document.getElementById("lng").value);
  data.append("cargadores", document.getElementById("cargadores").value);

  fetch(API_URL, { method: "POST", body: data })
    .then(r => r.json())
    .then(res => alert(res.success ? "Estación agregada" : "Error al agregar"));
});

// Eliminar estación
document.getElementById("form-eliminar").addEventListener("submit", e => {
  e.preventDefault();
  const id = document.getElementById("id-eliminar").value;
  fetch(API_URL, { method: "DELETE", body: `id=${id}` })
    .then(r => r.json())
    .then(res => alert(res.success ? "Estación eliminada" : "Error al eliminar"));
});

// Modificar estación
document.getElementById("form-modificar").addEventListener("submit", e => {
  e.preventDefault();
  const data = new URLSearchParams();
  data.append("id", document.getElementById("mod-id").value);
  data.append("nombre", document.getElementById("mod-nombre").value);
  data.append("direccion", document.getElementById("mod-direccion").value);
  data.append("departamento", document.getElementById("mod-departamento").value);
  data.append("lat", document.getElementById("mod-lat").value);
  data.append("lng", document.getElementById("mod-lng").value);
  data.append("cargadores", document.getElementById("mod-cargadores").value);

  fetch(API_URL, { method: "PUT", body: data })
    .then(r => r.json())
    .then(res => alert(res.success ? "Estación modificada" : "Error al modificar"));
});

// Listar estaciones
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
