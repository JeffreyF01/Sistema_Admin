<?php
require "conexion.php";

// 🧹 --- ELIMINAR REGISTRO ---
if (isset($_POST['eliminar'])) {
    $id = $_POST['eliminar']; // ID enviado desde AJAX

    // Preparamos eliminación
    $sql = $conexion->prepare("DELETE FROM ubicacion WHERE id_ubicaciones = ?");
    $sql->bind_param("i", $id);

    if ($sql->execute()) {
        echo "ok";
    } else {
        echo "Error al eliminar: " . $conexion->error;
    }
    exit(); // Importante para detener la ejecución
}

// 🧩 --- GUARDAR O ACTUALIZAR ---
$id = $_POST['id_ubicaciones'] ?? '';
$codigo = $_POST['codigo'];
$nombre = $_POST['nombre'];
$id_almacen = $_POST['id_almacen'];
$activo = $_POST['activo'];

// Si no tiene ID → INSERT
if ($id == '') {
    $sql = $conexion->prepare("
        INSERT INTO ubicacion (codigo, nombre, id_almacen, activo)
        VALUES (?, ?, ?, ?)
    ");
    $sql->bind_param("ssii", $codigo, $nombre, $id_almacen, $activo);

// Si tiene ID → UPDATE
} else {
    $sql = $conexion->prepare("
        UPDATE ubicacion 
        SET codigo = ?, nombre = ?, id_almacen = ?, activo = ?
        WHERE id_ubicaciones = ?
    ");
    $sql->bind_param("ssiii", $codigo, $nombre, $id_almacen, $activo, $id);
}

// Ejecutar consulta
if ($sql->execute()) {
    echo "ok";
} else {
    echo "Error: " . $conexion->error;
}
?>
