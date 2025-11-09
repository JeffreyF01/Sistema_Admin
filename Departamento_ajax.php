<?php
include("conexion.php");

$accion = $_POST['accion'] ?? '';

if ($accion === 'eliminar') {
    $id = $_POST['id'];
    $stmt = $conexion->prepare("DELETE FROM departamento WHERE id_departamentos=?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        echo "ok";
    } else {
        echo "error";
    }
    exit;
}

$id = $_POST['id_departamentos'] ?? '';
$nombre = $_POST['nombre'] ?? '';
$activo = $_POST['activo'] ?? 1;

if ($id == "") {
    $stmt = $conexion->prepare("INSERT INTO departamento (nombre, activo) VALUES (?, ?)");
    $stmt->bind_param("si", $nombre, $activo);
} else {
    $stmt = $conexion->prepare("UPDATE departamento SET nombre=?, activo=? WHERE id_departamentos=?");
    $stmt->bind_param("sii", $nombre, $activo, $id);
}

if ($stmt->execute()) {
    echo "ok";
} else {
    echo "Error al guardar";
}
?>
