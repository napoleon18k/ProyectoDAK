<?php
function conexion()
{
    return new PDO('mysql:host=db;dbname=sistema_viajes', 'pocholo', 'root');
}
?>
