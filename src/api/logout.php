<?php
session_start();

// Destruir todas las variables de sesión
$_SESSION = [];

// Destruir la sesión
session_destroy();

// Devolver un JSON indicando éxito
header('Content-Type: application/json');
echo json_encode(['success' => true]);
