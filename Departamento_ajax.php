<?php
include("conexion.php");

$id = $_POST['id_departamentos'];
$nombre = $_POST['nombre'];
$activo = $_POST['activo'];

if($id == ""){
    $stmt = $conexion->prepare("INSERT INTO departamento (nombre, activo) VALUES (?, ?)");
    $stmt->bind_param("si", $nombre, $activo);
} else {
    $stmt = $conexion->prepare("UPDATE departamento SET nombre=?, activo=? WHERE id_departamentos=?");
    $stmt->bind_param("sii", $nombre, $activo, $id);
}

if($stmt->execute()){
    echo "ok";
} else {
    echo "Error al guardar";
}
