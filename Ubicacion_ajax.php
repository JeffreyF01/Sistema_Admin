<?php
require "conexion.php";

$codigo = $_POST['codigo'];
$nombre = $_POST['nombre'];
$id_almacen = $_POST['id_almacen'];
$activo = $_POST['activo'];

$sql = $conexion->prepare("
INSERT INTO ubicacion (codigo, nombre, id_almacen, activo)
VALUES (?, ?, ?, ?)
");

$sql->bind_param("ssii", $codigo, $nombre, $id_almacen, $activo);

if($sql->execute()){
  echo "ok";
} else {
  echo "Error: " . $conexion->error;
}
