<?php
$host = "localhost";
$port = 3306;

$user = "root";
$pass = "";
$db   = "sitema_admin";

$conexion = new mysqli($host, $user, $pass, $db, $port);

if ($conexion->connect_error) {
  die("Error de conexión: " . $conexion->connect_error);
}
?>
