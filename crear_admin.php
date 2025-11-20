<?php
require_once __DIR__ . '/src/modelo/usuario.php'; // ajusta la ruta según tu proyecto

$usuarioObj = new Usuario();

// Datos del nuevo usuario
$nombreUsuario = "admin1";
$password = "admin1"; // contraseña en texto plano
$rol = "admin";
$correo= "admin@gmail.com";
// Hasheamos la contraseña con bcrypt
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

// Insertamos el usuario
$result = $usuarioObj->insertar($nombreUsuario, $hashedPassword, $rol, $correo);

if ($result) {
    echo "Usuario creado correctamente";
} else {
    echo "Error al crear el usuario";
}
