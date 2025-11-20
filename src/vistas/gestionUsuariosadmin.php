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
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
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
        <input type="text" id="usuario" placeholder="Usuario" required>
        <input type="email" id="correo" placeholder="Correo" required>
        <input type="password" id="password" placeholder="Contraseña" required>
        <select id="rol">
            <option value="admin">admin</option>
            <option value="cliente">cliente</option>
            <option value="gestor">gestor</option>
        </select>
        <button type="submit">Registrar</button>
    </form>

    <h2>Modificar Usuario</h2>
    <form id="form-modificar">
        <input type="number" id="id-modificar" placeholder="ID del usuario a modificar" required>
        <input type="text" id="nuevo-usuario" placeholder="Nuevo usuario (opcional)">
        <input type="email" id="nuevo-correo" placeholder="Nuevo correo (opcional)">
        <input type="password" id="nueva-password" placeholder="Nueva contraseña (opcional)">
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
        try { return JSON.parse(text); }
        catch { return { success: false, error: "Respuesta inválida del servidor" }; }
    } catch {
        return { success: false, error: "Error de conexión" };
    }
}

// AGREGAR USUARIO
document.getElementById("form-insertar").addEventListener("submit", async e => {
    e.preventDefault();

    const usuario = document.getElementById("usuario").value.trim();
    const correo = document.getElementById("correo").value.trim();
    const password = document.getElementById("password").value.trim();
    const rol = document.getElementById("rol").value;

    const data = await fetchAPI("../api/api.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `insertar=1&usuario=${encodeURIComponent(usuario)}&correo=${encodeURIComponent(correo)}&password=${encodeURIComponent(password)}&rol=${encodeURIComponent(rol)}`
    });

    alert(data.success ? "Usuario insertado correctamente" : data.error);
    listar();
});

// MODIFICAR USUARIO (POR ID)
document.getElementById("form-modificar").addEventListener("submit", async e => {
    e.preventDefault();

    const id = document.getElementById("id-modificar").value.trim();
    const usuario = document.getElementById("nuevo-usuario").value.trim();
    const correo = document.getElementById("nuevo-correo").value.trim();
    const password = document.getElementById("nueva-password").value.trim();

    const body =
        `id=${encodeURIComponent(id)}`
        + `&usuario=${encodeURIComponent(usuario)}`
        + `&correo=${encodeURIComponent(correo)}`
        + `&password=${encodeURIComponent(password)}`;

    const data = await fetchAPI("../api/api.php", {
        method: "PUT",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body
    });

    alert(data.success ? "Usuario modificado correctamente" : data.error);
    listar();
});

// LISTAR USUARIOS
document.getElementById("btn-listar").addEventListener("click", listar);

async function listar() {
    const data = await fetchAPI("../api/api.php?listar=1");
    let html = "";

    if (Array.isArray(data)) {
        data.forEach(u => {
            html += `
                <li>
                    ${u.id} - ${u.usuario} (${u.rol}) - ${u.correo}
                    <button onclick='eliminarUsuario(${u.id})'>Eliminar</button>
                </li>`;
        });
    } else {
        html = "<li>No hay usuarios.</li>";
    }

    document.getElementById("resultado").innerHTML = html;
}

// ELIMINAR USUARIO (POR ID)
async function eliminarUsuario(id) {
    if (!confirm("¿Seguro que quieres eliminar el usuario con ID " + id + "?")) return;

    const data = await fetchAPI("../api/api.php", {
        method: "DELETE",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `id=${encodeURIComponent(id)}`
    });

    alert(data.success ? "Usuario eliminado" : data.error);
    listar();
}

// cargar lista al abrir
listar();
</script>
</body>
</html>
