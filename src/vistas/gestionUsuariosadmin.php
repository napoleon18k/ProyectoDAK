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
  <title>GESTIÓN DE USUARIOS</title>
  <style>
    body { font-family: Arial; background: #f2f2f2; padding: 20px; }
    .container { max-width: 600px; margin: auto; background: #fff; padding: 20px; border-radius: 10px; }
    input, select, button { width: 100%; padding: 10px; margin: 5px 0; }
    ul#resultado { list-style: none; padding: 0; }
    ul#resultado li { padding: 5px 0; display: flex; justify-content: space-between; }
  </style>
</head>

<body>
  <div class="container">
    <h2>Agregar Usuario</h2>
    <form id="form-insertar">
      <input type="text" id="usuario" placeholder="Usuario" required />
      <input type="password" id="password" placeholder="Contraseña" required />
      <select name="rol" id="rol">
        <option value="admin">admin</option>
        <option value="cliente">cliente</option>
      </select>
      <button type="submit">Registrar</button>
    </form>

    <h2>Eliminar Usuario</h2>
    <form id="form-eliminar">
      <input type="text" id="usuario-eliminar" placeholder="Usuario a eliminar" required />
      <button type="submit">Eliminar</button>
    </form>

    <h2>Modificar Contraseña</h2>
    <form id="form-modificar">
      <input type="text" id="usuario-modificar" placeholder="Usuario" required />
      <input type="password" id="nueva-password" placeholder="Nueva contraseña" required />
      <button type="submit">Modificar</button>
    </form>

    <h2>Lista de Usuarios</h2>
    <button id="btn-listar">Listar Usuarios</button>
    <ul id="resultado"></ul>

    <button onclick="location.href='administrador.php'">Volver</button>
  </div>

  <script>
    async function fetchAPI(url, options = {}) {
      try {
        const res = await fetch(url, options);
        const text = await res.text();
        try {
          return JSON.parse(text);
        } catch (e) {
          console.error("Respuesta no es JSON:", text);
          return { success: false, error: "Respuesta inválida del servidor" };
        }
      } catch(err) {
        console.error(err);
        return { success: false, error: "Error de conexión" };
      }
    }

    document.getElementById("form-insertar").addEventListener("submit", async e => {
      e.preventDefault();
      const usuario = document.getElementById("usuario").value.trim();
      const password = document.getElementById("password").value.trim();
      const rol = document.getElementById("rol").value;

      if (!/^[a-zA-Z0-9_]+$/.test(usuario)) {
        alert("¡Error! Solo letras, números y guion bajo.");
        return;
      }

      const data = await fetchAPI("../api/api.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `insertar=1&usuario=${encodeURIComponent(usuario)}&password=${encodeURIComponent(password)}&rol=${encodeURIComponent(rol)}`
      });

      alert(data.success ? "Usuario insertado con éxito." : (data.error || "Error al insertar"));
      listar();
    });

    document.getElementById("form-eliminar").addEventListener("submit", async e => {
      e.preventDefault();
      const usuario = document.getElementById("usuario-eliminar").value.trim();
      if(!usuario) return;

      const data = await fetchAPI("../api/api.php", {
        method: "DELETE",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `usuario_sesion=${encodeURIComponent(usuario)}`
      });

      alert(data.success ? "Usuario eliminado." : (data.error || "Error al eliminar"));
      listar();
    });

    document.getElementById("form-modificar").addEventListener("submit", async e => {
      e.preventDefault();
      const usuario = document.getElementById("usuario-modificar").value.trim();
      const nuevaPassword = document.getElementById("nueva-password").value.trim();
      if(!usuario || !nuevaPassword) return;

      const data = await fetchAPI("../api/api.php", {
        method: "PUT",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `usuario=${encodeURIComponent(usuario)}&password=${encodeURIComponent(nuevaPassword)}`
      });

      alert(data.success ? "Contraseña modificada con éxito." : (data.error || "Error al modificar"));
      listar();
    });

    document.getElementById("btn-listar").addEventListener("click", listar);

    async function listar() {
      const data = await fetchAPI("../api/api.php?listar=1");
      let html = "";
      if(data && Array.isArray(data)) {
        data.forEach(u => {
          html += `<li>${u.id} - ${u.usuario} (${u.rol})
                    <button onclick='eliminarUsuario("${u.usuario}")'>Eliminar</button>
                  </li>`;
        });
      } else {
        html = "<li>No hay usuarios</li>";
      }
      document.getElementById("resultado").innerHTML = html;
    }

    async function eliminarUsuario(usuario) {
      const data = await fetchAPI("../api/api.php", {
        method: "DELETE",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `usuario_sesion=${encodeURIComponent(usuario)}`
      });

      alert(data.success ? "Usuario eliminado." : (data.error || "Error al eliminar"));
      listar();
    }

    // Cargar lista al inicio
    listar();
  </script>
</body>
</html>
