<?php
session_start(); 
// Inicia la sesión para poder acceder a las variables de sesión

// Verificamos si hay un usuario logueado y que tenga rol de cliente
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'cliente') {
    // Si no cumple, redirigimos al login
    header('Location: login.html');
    exit();
}

// Variables de sesión con valores por defecto en caso de que no existan
$usuario = $_SESSION['usuario'] ?? "Usuario";                  // Nombre del usuario logueado
$correo  = $_SESSION['correo']  ?? "correo@usuario.com";       // Correo del usuario
$foto    = $_SESSION['foto']    ?? "../assets/imagenes/user.jpg"; // Foto de perfil (ruta por defecto)
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gestión de Perfil</title>
  <!-- Estilo CSS externo para esta vista -->
  <link rel="stylesheet" href="../assets/css/gestionPerfil.css">
</head>
<body>

<div class="perfil-container">
  <!-- Contenedor principal del perfil -->

  <!-- Foto de perfil -->
  <div class="foto-perfil">
    <img id="fotoUsuario" src="" alt="Foto de usuario">
  </div>

  <!-- Datos del perfil -->
  <div class="datos-perfil">
    <h2 id="nombreUsuario">Nombre del usuario</h2>
    <p><strong>Correo:</strong> <span id="correoUsuario"></span></p>
    <p><strong>Password:</strong> <span id="passwordUsuario">********</span></p>

    <!-- Botones para gestionar perfil -->
    <div class="botones-perfil">
      <button id="modificarPerfilBtn">Modificar Perfil</button>
      <button id="guardarPerfilBtn" style="display:none;">Guardar Cambios</button>
      <button id="eliminarPerfilBtn">Eliminar Cuenta</button>
      <button class="btn-volver" onclick="window.location.href='PrincipalCliente.html'">Volver a Principal</button>
    </div>
  </div>
</div>

<script>
  // Variables traídas desde PHP (sesión) hacia JavaScript
  const usuario_duenio = "<?php echo $usuario; ?>";
  const correo_duenio = "<?php echo $correo; ?>";
  const password_duenio = "********"; // No se muestra la real, por seguridad
  const foto_duenio = "<?php echo $foto; ?>";

  // Seleccionamos los elementos del DOM
  const nombreUsuarioEl = document.getElementById('nombreUsuario');
  const correoUsuarioEl = document.getElementById('correoUsuario');
  const passwordUsuarioEl = document.getElementById('passwordUsuario');
  const fotoUsuarioEl = document.getElementById('fotoUsuario');

  // Cargamos los datos iniciales en pantalla
  nombreUsuarioEl.textContent = usuario_duenio;
  correoUsuarioEl.textContent = correo_duenio;
  passwordUsuarioEl.textContent = password_duenio;
  fotoUsuarioEl.src = foto_duenio;

  // Referencias a los botones
  const modificarBtn = document.getElementById('modificarPerfilBtn');
  const guardarBtn = document.getElementById('guardarPerfilBtn');

  // Evento para modificar perfil → reemplaza el texto por inputs editables
  modificarBtn.addEventListener('click', () => {
    nombreUsuarioEl.innerHTML = `<input type="text" id="inputNombre" value="${usuario_duenio}">`;
    correoUsuarioEl.innerHTML = `<input type="email" id="inputCorreo" value="${correo_duenio}">`;
    passwordUsuarioEl.innerHTML = `<input type="password" id="inputPassword" placeholder="Nueva contraseña">`;

    // Mostramos botón Guardar y ocultamos Modificar
    guardarBtn.style.display = "inline-block";
    modificarBtn.style.display = "none";
  });

  // Evento para guardar cambios del perfil
  guardarBtn.addEventListener('click', () => {
    const nuevoNombre = document.getElementById('inputNombre').value.trim();
    const nuevoCorreo = document.getElementById('inputCorreo').value.trim();
    const nuevoPassword = document.getElementById('inputPassword').value.trim();

    // Validación básica
    if(!nuevoNombre || !nuevoCorreo) {
      alert("Nombre y correo no pueden estar vacíos.");
      return;
    }

    // Petición PUT a la API para actualizar datos en la DB
    fetch('/src/api/api.php', {
      method: 'PUT',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `usuario=${encodeURIComponent(nuevoNombre)}&correo=${encodeURIComponent(nuevoCorreo)}&password=${encodeURIComponent(nuevoPassword)}`
    })
    .then(res => res.json())
    .then(data => {
      if(data.success) {
        // ✅ Actualizamos la vista en pantalla
        nombreUsuarioEl.textContent = nuevoNombre;
        correoUsuarioEl.textContent = nuevoCorreo;
        passwordUsuarioEl.textContent = "********";

        guardarBtn.style.display = "none";
        modificarBtn.style.display = "inline-block";

        alert("Perfil actualizado correctamente.");

        // ✅ Y actualizamos también la sesión en el servidor
        fetch('/src/api/actualizarSesion.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: `usuario=${encodeURIComponent(nuevoNombre)}&correo=${encodeURIComponent(nuevoCorreo)}`
        })
        .then(res => res.json())
        .then(sessionData => {
          console.log("Sesión actualizada:", sessionData);
        })
        .catch(err => console.error("Error actualizando la sesión:", err));

      } else {
        alert("Error al actualizar el perfil.");
      }
    })
    .catch(err => console.error(err));
  });

  // Evento para eliminar la cuenta
  const eliminarBtn = document.getElementById('eliminarPerfilBtn');
  eliminarBtn.addEventListener('click', () => {
    if(confirm("¿Seguro que deseas eliminar tu cuenta?")) {
      fetch('/src/api/api.php', {
        method: 'DELETE',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'}
        // No mandamos ID porque la API identifica por sesión
      })
      .then(res => res.json())
      .then(data => {
        if(data.success) {
          alert("Cuenta eliminada correctamente.");
          window.location.href = "login.html"; // Redirige al login
        } else {
          alert("Error al eliminar la cuenta.");
        }
      });
    }
  });
</script>

</body>
</html>
