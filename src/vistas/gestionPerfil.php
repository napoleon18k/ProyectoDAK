<?php
session_start(); 
// Iniciamos sesión para acceder a variables de sesión

// Variables de sesión con valores por defecto
$usuario = $_SESSION['usuario'] ?? "Usuario";
$correo  = $_SESSION['correo']  ?? "correo@usuario.com";
$foto    = $_SESSION['foto']    ?? "../assets/imagenes/user.jpg";
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
  <!-- Contenedor de perfil: foto y datos del usuario -->
  <div class="foto-perfil">
    <img id="fotoUsuario" src="" alt="Foto de usuario">
  </div>

  <div class="datos-perfil">
    <!-- Datos del usuario -->
    <h2 id="nombreUsuario">Nombre del usuario</h2>
    <p><strong>Correo:</strong> <span id="correoUsuario"></span></p>
    <p><strong>Password:</strong> <span id="passwordUsuario">********</span></p>

    <!-- Botones de acciones -->
    <div class="botones-perfil">
      <button id="modificarPerfilBtn">Modificar Perfil</button>
      <button id="guardarPerfilBtn" style="display:none;">Guardar Cambios</button>
      <button id="eliminarPerfilBtn">Eliminar Cuenta</button>
    </div>
  </div>
</div>

<script>
  const usuario_duenio = "<?php echo $usuario; ?>";
  const correo_duenio = "<?php echo $correo; ?>";
  const password_duenio = "********";
  const foto_duenio = "<?php echo $foto; ?>";

  const nombreUsuarioEl = document.getElementById('nombreUsuario');
  const correoUsuarioEl = document.getElementById('correoUsuario');
  const passwordUsuarioEl = document.getElementById('passwordUsuario');
  const fotoUsuarioEl = document.getElementById('fotoUsuario');

  nombreUsuarioEl.textContent = usuario_duenio;
  correoUsuarioEl.textContent = correo_duenio;
  passwordUsuarioEl.textContent = password_duenio;
  fotoUsuarioEl.src = foto_duenio;

  const modificarBtn = document.getElementById('modificarPerfilBtn');
  const guardarBtn = document.getElementById('guardarPerfilBtn');

  modificarBtn.addEventListener('click', () => {
    nombreUsuarioEl.innerHTML = `<input type="text" id="inputNombre" value="${usuario_duenio}">`;
    correoUsuarioEl.innerHTML = `<input type="email" id="inputCorreo" value="${correo_duenio}">`;
    passwordUsuarioEl.innerHTML = `<input type="password" id="inputPassword" placeholder="Nueva contraseña">`;

    guardarBtn.style.display = "inline-block";
    modificarBtn.style.display = "none";
  });

  guardarBtn.addEventListener('click', () => {
    const nuevoNombre = document.getElementById('inputNombre').value.trim();
    const nuevoCorreo = document.getElementById('inputCorreo').value.trim();
    const nuevoPassword = document.getElementById('inputPassword').value.trim();

    // Validar que no estén vacíos
    if(!nuevoNombre || !nuevoCorreo) {
      alert("Nombre y correo no pueden estar vacíos.");
      return;
    }

    // 🔹 Fetch para actualizar perfil en la API
    fetch('api/api.php', {
      method: 'PUT',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `usuario=${encodeURIComponent(nuevoNombre)}&correo=${encodeURIComponent(nuevoCorreo)}&password=${encodeURIComponent(nuevoPassword)}`
    })
    .then(res => res.json())
    .then(data => {
      if(data.success) {
        // Actualizar vista
        nombreUsuarioEl.textContent = nuevoNombre;
        correoUsuarioEl.textContent = nuevoCorreo;
        passwordUsuarioEl.textContent = "********";

        guardarBtn.style.display = "none";
        modificarBtn.style.display = "inline-block";

        alert("Perfil actualizado correctamente.");

        // 🔹 Actualizar sesión en el servidor (opcional)
        fetch('api/actualizarSesion.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: `usuario=${encodeURIComponent(nuevoNombre)}&correo=${encodeURIComponent(nuevoCorreo)}`
        });
      } else {
        alert("Error al actualizar el perfil.");
      }
    })
    .catch(err => console.error(err));
  });

  const eliminarBtn = document.getElementById('eliminarPerfilBtn');
  eliminarBtn.addEventListener('click', () => {
    if(confirm("¿Seguro que deseas eliminar tu cuenta?")) {
      fetch('api/api.php', {
        method: 'DELETE',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'}
        // No es necesario mandar ID si la API usa sesión
      })
      .then(res => res.json())
      .then(data => {
        if(data.success) {
          alert("Cuenta eliminada correctamente.");
          window.location.href = "login.html";
        } else {
          alert("Error al eliminar la cuenta.");
        }
      });
    }
  });
</script>

</body>
</html>
