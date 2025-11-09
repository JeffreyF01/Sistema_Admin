<?php
require "conexion.php";

$accion = $_POST['accion'] ?? '';

if ($accion == 'eliminar') {
    $id = $_POST['id_tipos_movimiento'];
    $sql = $conexion->prepare("DELETE FROM tipo_movimiento WHERE id_tipos_movimiento = ?");
    $sql->bind_param("i", $id);
    echo $sql->execute() ? "ok" : "Error: " . $conexion->error;
    exit;
}

$id = $_POST['id_tipos_movimiento'] ?? '';
$nombre = $_POST['nombre'];
$clase = $_POST['clase'];
$descripcion = $_POST['descripcion'];
$activo = $_POST['activo'];

if ($id == '') {
    $sql = $conexion->prepare("INSERT INTO tipo_movimiento (nombre, clase, descripcion, activo) VALUES (?, ?, ?, ?)");
    $sql->bind_param("sssi", $nombre, $clase, $descripcion, $activo);
} else {
    $sql = $conexion->prepare("UPDATE tipo_movimiento SET nombre=?, clase=?, descripcion=?, activo=? WHERE id_tipos_movimiento=?");
    $sql->bind_param("sssii", $nombre, $clase, $descripcion, $activo, $id);
}

echo $sql->execute() ? "ok" : "Error: " . $conexion->error;
?>
