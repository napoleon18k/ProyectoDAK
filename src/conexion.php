<?php
function conexion()
{
    return new PDO('mysql:host=db;dbname=DAKdb', 'pocholo', 'root');
}
?>
