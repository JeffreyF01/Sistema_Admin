<?php
require "conexion.php";

$nombre = $_POST['nombre'];
$clase = $_POST['clase'];
$descripcion = $_POST['descripcion'];
$activo = $_POST['activo'];

$sql = $conexion->prepare("
INSERT INTO tipo_movimiento (nombre, clase, descripcion, activo) 
VALUES (?, ?, ?, ?)
");

$sql->bind_param("sssi", $nombre, $clase, $descripcion, $activo);

if($sql->execute()){
  echo "ok";
}else{
  echo "Error: " . $conexion->error;
}
?>
