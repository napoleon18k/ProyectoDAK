<?php
session_start(); 
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'cliente') {
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
    <link rel="stylesheet" href="../assets/css/gestionVehiculosCliente.css">
</head>
<body>
  <h1>Gestión de mis Vehículos</h1>
  <div class="main-container">
    
        <div class="menu-lateral">
      <img src="../assets/imagenes/agregarAuto.png" alt="Agregar" data-seccion="agregar">
      <img src="../assets/imagenes/modificarAuto.png" alt="Modificar" data-seccion="modificar">
      <img src="../assets/imagenes/eliminarAuto.png" alt="Eliminar" data-seccion="eliminar">
      <img src="../assets/imagenes/listaAutos.png" alt="Listar" data-seccion="listar">
    </div>

        <div class="contenido">

            <div id="agregar" class="seccion">
        <form id="form-agregar">
          <input type="text" id="marca" placeholder="Marca" required />
          <input type="text" id="modelo" placeholder="Modelo" required />
          <input type="number" id="ano" placeholder="Año" required min="1990" max="<?php echo date("Y"); ?>" />
          <input type="text" id="matricula" placeholder="Matrícula (Ej: AAA1234)" required maxlength="7" pattern="[A-Za-z]{3}[0-9]{4}" title="Debe ser 3 letras y 4 números (Ej: AAA1234)" />
          <input type="number" id="autonomia" placeholder="Autonomía (km)" required min="50" />
          <select id="tipoconector">
            <option value="lenta">Lenta</option>
            <option value="rapida">Rápida</option>
            <option value="ultra">Ultra</option>
          </select>
          <button type="submit" class="submit-btn">Agregar Vehículo</button>
        </form>
      </div>

            <div id="modificar" class="seccion">
        <form id="form-modificar">
          <input type="number" id="id-modificar" placeholder="ID de vehículo" required min="1" />
          <input type="number" id="nueva-autonomia" placeholder="Nueva autonomía (km)" required min="50" />
          <select id="tipoconector-mod">
            <option value="lenta">Lenta</option>
            <option value="rapida">Rápida</option>
            <option value="ultra">Ultra</option>
          </select>
          <button type="submit" class="submit-btn">Modificar Vehículo</button>
        </form>
      </div>

            <div id="eliminar" class="seccion">
        <form id="form-eliminar">
          <input type="number" id="id-eliminar" placeholder="ID a eliminar" required min="1" />
          <button type="submit" class="submit-btn">Eliminar Vehículo</button>
        </form>
      </div>

            <div id="listar" class="seccion">
        <div class="vehiculos-tarjetas" id="vehiculosTarjetas"></div>
      </div>

            <button class="volver-btn" onclick="location.href='PrincipalCliente.html'">Volver</button>
    </div>
  </div>

  <script>

  const botonesMenu = document.querySelectorAll('.menu-lateral img'); 
  const secciones = document.querySelectorAll('.seccion');           
  const tarjetasDiv = document.getElementById('vehiculosTarjetas');  

 
  secciones.forEach(s => s.style.display = 'none');

  // Evento de clic en cada botón del menú lateral
  botonesMenu.forEach(btn => {
    btn.addEventListener('click', () => {
      const seccion = document.getElementById(btn.dataset.seccion);

      
      if(seccion.style.display === 'block'){
        seccion.style.display = 'none';
        tarjetasDiv.innerHTML = '';
        btn.classList.remove('active');
        return;
      }

      
      secciones.forEach(s => s.style.display = 'none');
      botonesMenu.forEach(b => b.classList.remove('active'));

     
      seccion.style.display = 'block';
      btn.classList.add('active');

      
      if(btn.dataset.seccion === 'listar'){
        listarVehiculos();
      } else {
        tarjetasDiv.innerHTML = '';
      }
    });
  });


  // Validacón genérica para el formulario AGREGAR
  function validarFormularioAgregar(marca, modelo, ano, matricula, autonomia) {
    if (marca.length < 2) {
      alert("La marca debe tener al menos 2 caracteres.");
      return false;
    }
    if (modelo.length < 2) {
      alert("El modelo debe tener al menos 2 caracteres.");
      return false;
    }
    
    const anoActual = new Date().getFullYear();
    if (parseInt(ano) < 1990 || parseInt(ano) > anoActual) {
      alert(`El año debe ser un número válido entre 1990 y ${anoActual}.`);
      return false;
    }
    
    // Ej: AAA1234
    const regexMatricula = /^[A-Za-z]{3}\d{4}$/;
    if (!regexMatricula.test(matricula)) {
      alert("La matrícula debe tener el formato LLLNNNN (Ej: AAA1234).");
      return false;
    }
    
    if (parseInt(autonomia) < 50) {
      alert("La autonomía debe ser de al menos 50 km.");
      return false;
    }
    return true;
  }
  
  // POST
  document.getElementById("form-agregar").addEventListener("submit", e => {
    e.preventDefault(); 

    
    const marca = document.getElementById("marca").value.trim();
    const modelo = document.getElementById("modelo").value.trim();
    const ano = document.getElementById("ano").value.trim();
    const matricula = document.getElementById("matricula").value.trim().toUpperCase();
    const autonomia = document.getElementById("autonomia").value.trim();
    const tipo_conector = document.getElementById("tipoconector").value;

    
    if (!validarFormularioAgregar(marca, modelo, ano, matricula, autonomia)) {
      return; 
    }

    
    fetch("../api/apiVehiculos.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: `insertarV=1&marca=${encodeURIComponent(marca)}&modelo=${encodeURIComponent(modelo)}&ano=${encodeURIComponent(ano)}&matricula=${encodeURIComponent(matricula)}&autonomia=${encodeURIComponent(autonomia)}&tipo_conector=${encodeURIComponent(tipo_conector)}`
    })
    .then(res => res.json())
    .then(data => {
      alert(data.success ? "Vehículo insertado con éxito." : data.error || "Error al insertar vehículo.");
      document.getElementById("form-agregar").reset(); 
      listarVehiculos(); 
    })
    .catch(error => {
      console.error('Error al enviar la solicitud:', error);
      alert("Hubo un error de conexión al agregar el vehículo.");
    });
  });

  // PUT
  document.getElementById("form-modificar").addEventListener("submit", e => {
    e.preventDefault();

    const id = document.getElementById("id-modificar").value.trim();
    const autonomia = document.getElementById("nueva-autonomia").value.trim();
    const tipo_conector = document.getElementById("tipoconector-mod").value;

    
    if (!id || parseInt(id) <= 0) {
      alert("El ID del vehículo debe ser un número positivo.");
      return;
    }
    if (!autonomia || parseInt(autonomia) < 50) {
      alert("La nueva autonomía debe ser de al menos 50 km.");
      return;
    }

    fetch("../api/apiVehiculos.php", {
      method: "PUT",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: `id=${id}&nuevaautonomia=${encodeURIComponent(autonomia)}&tipo_conector=${encodeURIComponent(tipo_conector)}`
    })
    .then(res => res.json())
    .then(data => {
      alert(data.success ? "Vehículo modificado con éxito." : data.error || "Error al modificar vehículo.");
      document.getElementById("form-modificar").reset();
      listarVehiculos();
    })
    .catch(error => {
      console.error('Error al enviar la solicitud:', error);
      alert("Hubo un error de conexión al modificar el vehículo.");
    });
  });

  // DELETE
  document.getElementById("form-eliminar").addEventListener("submit", e => {
    e.preventDefault();

    const id = document.getElementById("id-eliminar").value.trim();

    
    if (!id || parseInt(id) <= 0) {
      alert("El ID a eliminar debe ser un número positivo.");
      return;
    }
    
    // Pedir confirmación antes de eliminar
    if (!confirm(`¿Estás seguro de que quieres eliminar el vehículo con ID ${id}?`)) {
      return;
    }

    fetch("../api/apiVehiculos.php", {
      method: "DELETE",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: `id=${id}`
    })
    .then(res => res.json())
    .then(data => {
      alert(data.success ? "Vehículo eliminado." : data.error || "Error al eliminar vehículo.");
      document.getElementById("form-eliminar").reset();
      listarVehiculos();
    })
    .catch(error => {
      console.error('Error al enviar la solicitud:', error);
      alert("Hubo un error de conexión al eliminar el vehículo.");
    });
  });

  
  function listarVehiculos(){
    tarjetasDiv.innerHTML = 'Cargando vehículos...';
    fetch("../api/apiVehiculos.php?listar=1")
      .then(res => res.json())
      .then(data => {
        let tarjetasHtml = "";
        
        if (data.length === 0) {
            tarjetasHtml = "<p>No tienes vehículos registrados.</p>";
        } else {
            // Generamos una tarjeta por cada vehículo recibido
            data.forEach(vehiculo => {
                tarjetasHtml += `<div class="tarjeta">
                                     <p>ID: ${vehiculo.id}</p>
                                     <img src="../assets/imagenes/vehiculo_placeholder.png">
                                     <p>${vehiculo.marca} - ${vehiculo.modelo} (${vehiculo.ano})</p>
                                     <p>Matrícula: ${vehiculo.matricula}</p>
                                     <p>Autonomía: ${vehiculo.autonomia} km</p>
                                     <p>Conector: ${vehiculo.tipo_conector}</p>
                                </div>`;
            });
        }
        tarjetasDiv.innerHTML = tarjetasHtml; 
      })
      .catch(error => {
        tarjetasDiv.innerHTML = "<p>Error al cargar la lista de vehículos.</p>";
        console.error('Error al listar vehículos:', error);
      });
  }
</script>
</body>
</html>