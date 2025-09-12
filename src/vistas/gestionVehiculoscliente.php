<?php
session_start(); 
// Iniciamos sesión para acceder a las variables guardadas

// Verificamos si hay usuario logueado y que su rol sea "cliente"
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'cliente') {
    // Si no cumple, lo redirigimos al login
    header('Location: login.html');
    exit();
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gestión de mis Vehículos</title>
  <!-- Estilos propios de esta vista -->
  <link rel="stylesheet" href="../assets/css/gestionVehiculosCliente.css">
</head>
<body>
  <h1>Gestión de mis Vehículos</h1>
  <div class="main-container">
    
    <!-- Menú lateral con íconos de acciones -->
    <div class="menu-lateral">
      <img src="../assets/imagenes/agregarAuto.png" alt="Agregar" data-seccion="agregar">
      <img src="../assets/imagenes/modificarAuto.png" alt="Modificar" data-seccion="modificar">
      <img src="../assets/imagenes/eliminarAuto.png" alt="Eliminar" data-seccion="eliminar">
      <img src="../assets/imagenes/listaAutos.png" alt="Listar" data-seccion="listar">
    </div>

    <!-- Contenido dinámico según acción seleccionada -->
    <div class="contenido">

      <!-- Formulario para AGREGAR vehículo -->
      <div id="agregar" class="seccion">
        <form id="form-agregar">
          <input type="text" id="marca" placeholder="Marca" required />
          <input type="text" id="modelo" placeholder="Modelo" required />
          <input type="text" id="ano" placeholder="Año" required />
          <input type="text" id="matricula" placeholder="Matrícula" required />
          <input type="text" id="autonomia" placeholder="Autonomía" required />
          <select id="tipoconector">
            <option value="lenta">Lenta</option>
            <option value="rapida">Rápida</option>
            <option value="ultra">Ultra</option>
          </select>
          <button type="submit" class="submit-btn">Agregar Vehículo</button>
        </form>
      </div>

      <!-- Formulario para MODIFICAR vehículo -->
      <div id="modificar" class="seccion">
        <form id="form-modificar">
          <input type="number" id="id-modificar" placeholder="ID de vehículo" required />
          <input type="text" id="nueva-autonomia" placeholder="Nueva autonomía" required />
          <select id="tipoconector-mod">
            <option value="lenta">Lenta</option>
            <option value="rapida">Rápida</option>
            <option value="ultra">Ultra</option>
          </select>
          <button type="submit" class="submit-btn">Modificar Vehículo</button>
        </form>
      </div>

      <!-- Formulario para ELIMINAR vehículo -->
      <div id="eliminar" class="seccion">
        <form id="form-eliminar">
          <input type="number" id="id-eliminar" placeholder="ID a eliminar" required />
          <button type="submit" class="submit-btn">Eliminar Vehículo</button>
        </form>
      </div>

      <!-- Sección para LISTAR vehículos -->
      <div id="listar" class="seccion">
        <div class="vehiculos-tarjetas" id="vehiculosTarjetas"></div>
      </div>

      <!-- Botón para volver al menú principal -->
      <button class="volver-btn" onclick="location.href='PrincipalCliente.html'">Volver</button>
    </div>
  </div>

  <script>
  // Selección de elementos principales
  const botonesMenu = document.querySelectorAll('.menu-lateral img'); // Íconos del menú
  const secciones = document.querySelectorAll('.seccion');           // Todas las secciones
  const tarjetasDiv = document.getElementById('vehiculosTarjetas');  // Contenedor de tarjetas (listar)

  // Al inicio, ocultamos todas las secciones
  secciones.forEach(s => s.style.display = 'none');

  // Evento de clic en cada botón del menú lateral
  botonesMenu.forEach(btn => {
    btn.addEventListener('click', () => {
      const seccion = document.getElementById(btn.dataset.seccion);

      // Si la sección ya estaba visible, se oculta
      if(seccion.style.display === 'block'){
        seccion.style.display = 'none';
        tarjetasDiv.innerHTML = '';
        btn.classList.remove('active');
        return;
      }

      // Ocultamos todas las secciones y desmarcamos botones
      secciones.forEach(s => s.style.display = 'none');
      botonesMenu.forEach(b => b.classList.remove('active'));

      // Mostramos la sección seleccionada y activamos el botón
      seccion.style.display = 'block';
      btn.classList.add('active');

      // Si es la opción "listar", llamamos a la función que trae los vehículos
      if(btn.dataset.seccion === 'listar'){
        listarVehiculos();
      } else {
        tarjetasDiv.innerHTML = '';
      }
    });
  });

  // ---- CRUD VEHÍCULOS ---- //

  // AGREGAR vehículo (método POST)
  document.getElementById("form-agregar").addEventListener("submit", e => {
    e.preventDefault(); // Evitamos recargar la página

    // Obtenemos valores del formulario
    const marca = document.getElementById("marca").value;
    const modelo = document.getElementById("modelo").value;
    const ano = document.getElementById("ano").value;
    const matricula = document.getElementById("matricula").value;
    const autonomia = document.getElementById("autonomia").value;
    const tipo_conector = document.getElementById("tipoconector").value;

    // Enviamos los datos a la API
    fetch("../api/apiVehiculos.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: `insertarV=1&marca=${encodeURIComponent(marca)}&modelo=${encodeURIComponent(modelo)}&ano=${encodeURIComponent(ano)}&matricula=${encodeURIComponent(matricula)}&autonomia=${encodeURIComponent(autonomia)}&tipo_conector=${encodeURIComponent(tipo_conector)}`
    })
    .then(res => res.json())
    .then(data => {
      alert(data.success ? "Vehículo insertado con éxito." : "Error al insertar vehículo.");
      listarVehiculos(); // Refrescamos la lista
    });
  });

  // MODIFICAR vehículo (método PUT)
  document.getElementById("form-modificar").addEventListener("submit", e => {
    e.preventDefault();

    const id = document.getElementById("id-modificar").value;
    const autonomia = document.getElementById("nueva-autonomia").value;
    const tipo_conector = document.getElementById("tipoconector-mod").value;

    fetch("../api/apiVehiculos.php", {
      method: "PUT",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: `id=${id}&nuevaautonomia=${encodeURIComponent(autonomia)}&tipo_conector=${encodeURIComponent(tipo_conector)}`
    })
    .then(res => res.json())
    .then(data => {
      alert(data.success ? "Vehículo modificado con éxito." : "Error al modificar vehículo.");
      listarVehiculos();
    });
  });

  // ELIMINAR vehículo (método DELETE)
  document.getElementById("form-eliminar").addEventListener("submit", e => {
    e.preventDefault();

    const id = document.getElementById("id-eliminar").value;

    fetch("../api/apiVehiculos.php", {
      method: "DELETE",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: `id=${id}`
    })
    .then(res => res.json())
    .then(data => {
      alert(data.success ? "Vehículo eliminado." : "Error al eliminar vehículo.");
      listarVehiculos();
    });
  });

  // LISTAR vehículos (método GET)
  function listarVehiculos(){
    fetch("../api/apiVehiculos.php?listar=1")
      .then(res => res.json())
      .then(data => {
        let tarjetasHtml = "";
        // Generamos una tarjeta por cada vehículo recibido
        data.forEach(vehiculo => {
          tarjetasHtml += `<div class="tarjeta">
                              <img src="../assets/imagenes/vehiculo_placeholder.png" alt="${vehiculo.marca} ${vehiculo.modelo}">
                              <p>${vehiculo.marca} - ${vehiculo.modelo}</p>
                           </div>`;
        });
        tarjetasDiv.innerHTML = tarjetasHtml; // Renderizamos tarjetas
      });
  }
</script>
</body>
</html>
