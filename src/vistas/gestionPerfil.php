<?php
session_start();

// Bloqueo si no hay cliente logueado
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'cliente') {
    header('Location: login.html');
    exit();
}

// Traigo datos desde la sesión
$usuario = $_SESSION['usuario'];
$correo  = $_SESSION['correo'];
$foto    = $_SESSION['foto'] ?? "../assets/imagenes/user.jpg";
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gestión de Perfil</title>
  <link rel="stylesheet" href="../assets/css/gestionPerfil.css">
</head>
<body>

<div class="perfil-container">

  <div class="foto-perfil">
    <img id="fotoUsuario" src="" alt="Foto de usuario">
  </div>

  <div class="datos-perfil">
    <h2 id="nombreUsuario"></h2>
    <p><strong>Correo:</strong> <span id="correoUsuario"></span></p>
    <p><strong>Password:</strong> <span id="passwordUsuario">********</span></p>

    <div class="botones-perfil">
      <button id="modificarPerfilBtn">Modificar Perfil</button>
      <button id="guardarPerfilBtn" style="display:none;">Guardar Cambios</button>

      <!-- BOTÓN ELIMINAR CUENTA -->
      <button id="eliminarPerfilBtn" style="background:#e74c3c;color:white;">Eliminar Cuenta</button>

      <button class="btn-volver" onclick="window.location.href='PrincipalCliente.html'">Volver a Principal</button>
    </div>
  </div>

</div>

<script>
  // Datos desde PHP (sesión)
  const usuario_duenio = "<?= $usuario ?>";
  const correo_duenio  = "<?= $correo ?>";
  const foto_duenio    = "<?= $foto ?>";

  // Elementos del DOM
  const nombreUsuarioEl = document.getElementById('nombreUsuario');
  const correoUsuarioEl = document.getElementById('correoUsuario');
  const passwordUsuarioEl = document.getElementById('passwordUsuario');
  const fotoUsuarioEl = document.getElementById('fotoUsuario');

  // Cargar valores en pantalla
  nombreUsuarioEl.textContent = usuario_duenio;
  correoUsuarioEl.textContent = correo_duenio;
  fotoUsuarioEl.src = foto_duenio;

  // Botones
  const modificarBtn = document.getElementById('modificarPerfilBtn');
  const guardarBtn   = document.getElementById('guardarPerfilBtn');
  const eliminarBtn  = document.getElementById('eliminarPerfilBtn');

  /* =====================================================
     EDITAR PERFIL
  ======================================================= */
  modificarBtn.addEventListener('click', () => {

    nombreUsuarioEl.innerHTML = `<input type="text" id="inputNombre" value="${usuario_duenio}">`;
    correoUsuarioEl.innerHTML = `<input type="email" id="inputCorreo" value="${correo_duenio}">`;
    passwordUsuarioEl.innerHTML = `<input type="password" id="inputPassword" placeholder="Nueva contraseña (opcional)">`;

    guardarBtn.style.display = "inline-block";
    modificarBtn.style.display = "none";
  });

  guardarBtn.addEventListener('click', () => {

    const nuevoNombre = document.getElementById('inputNombre').value.trim();
    const nuevoCorreo = document.getElementById('inputCorreo').value.trim();
    const nuevoPass   = document.getElementById('inputPassword').value.trim();

    if (!nuevoNombre || !nuevoCorreo) {
      alert("Nombre y correo no pueden estar vacíos");
      return;
    }

    fetch("http://localhost:8085/src/api/api.php", {
      method: "PUT",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: `usuario=${encodeURIComponent(nuevoNombre)}&correo=${encodeURIComponent(nuevoCorreo)}&password=${encodeURIComponent(nuevoPass)}`
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {

        alert("Perfil actualizado correctamente");

        nombreUsuarioEl.textContent = nuevoNombre;
        correoUsuarioEl.textContent = nuevoCorreo;
        passwordUsuarioEl.textContent = "********";

        guardarBtn.style.display = "none";
        modificarBtn.style.display = "inline-block";

        // Actualiza sesión
        fetch("http://localhost:8085/src/api/actualizarSesion.php", {
          method: "POST",
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
          body: `usuario=${encodeURIComponent(nuevoNombre)}&correo=${encodeURIComponent(nuevoCorreo)}`
        });

      } else {
        alert(data.error || "Error al actualizar perfil");
      }
    })
    .catch(err => console.error("Error:", err));

  });

  /* =====================================================
     ELIMINAR CUENTA (CLIENTE)
  ======================================================= */

  eliminarBtn.addEventListener('click', () => {

    if (!confirm("¿Seguro que deseas eliminar tu cuenta? Esta acción no tiene vuelta atrás.")) {
      return;
    }

    fetch("http://localhost:8085/src/api/api.php", {
      method: "DELETE",
      headers: { "Content-Type": "application/x-www-form-urlencoded" }
    })
    .then(res => res.json())
    .then(data => {

      if (data.success) {
        alert("Cuenta eliminada correctamente");
        window.location.href = "login.html";
      } else {
        alert(data.error || "Error al eliminar la cuenta");
      }

    })
    .catch(err => console.error("Error:", err));

  });

</script>

</body>
</html>
