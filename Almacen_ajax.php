<?php
require "conexion.php";

$codigo = $_POST['codigo'];
$nombre = $_POST['nombre'];
$activo = $_POST['activo'];

$sql = $conexion->prepare("
INSERT INTO almacen (codigo, nombre, activo)
VALUES (?, ?, ?)
");

$sql->bind_param("ssi", $codigo, $nombre, $activo);

if($sql->execute()){
  echo "ok";
}else{
  echo "Error: " . $conexion->error;
}
