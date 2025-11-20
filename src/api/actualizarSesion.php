<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Si llegan datos desde el fetch
    if (isset($_POST['usuario'])) {
        $_SESSION['usuario'] = $_POST['usuario'];
    }
    if (isset($_POST['correo'])) {
        $_SESSION['correo'] = $_POST['correo'];
    }
    if (isset($_POST['foto'])) { // opcional
        $_SESSION['foto'] = $_POST['foto'];
    }

    echo json_encode([
        'success' => true,
        'message' => 'Sesión actualizada correctamente',
        'usuario' => $_SESSION['usuario'],
        'correo'  => $_SESSION['correo']
    ]);
    exit();
}

// Si no es POST → error
http_response_code(405);
echo json_encode([
    'success' => false,
    'message' => 'Método no permitido'
]);
