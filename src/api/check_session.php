<?php
session_start();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

if (isset($_SESSION['usuario'])) {
    echo json_encode([
        'loggedIn' => true,
        'usuario' => $_SESSION['usuario'],
        'rol' => $_SESSION['rol'],
        'correo' => $_SESSION['correo'] ?? 'Correo no disponible'
    ]);
} else {
    echo json_encode([
        'loggedIn' => false
    ]);
}
