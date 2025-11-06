<?php
require "conexion.php";

$departamento_id = $_POST['departamento_id'];
$nombre = $_POST['nombre'];
$activo = $_POST['activo'];

$sql = $conexion->prepare("INSERT INTO grupo (departamento_id, nombre, activo) VALUES (?, ?, ?)");
$sql->bind_param("isi", $departamento_id, $nombre, $activo);

if($sql->execute()){
  echo "ok";
}else{
  echo "Error: " . $conexion->error;
}
?>
