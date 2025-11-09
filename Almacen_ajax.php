<?php
require "conexion.php";

if(isset($_POST['eliminar'])) {
    $id = $_POST['id_almacen'] ?? '';
    if($id == ''){
        echo "Error: ID no recibido";
        exit();
    }

    $sql = $conexion->prepare("DELETE FROM almacen WHERE id_almacen = ?");
    $sql->bind_param("i", $id);
    if($sql->execute()){
        echo "ok";
    } else {
        echo "Error: " . $conexion->error;
    }
    exit();
}

$id = $_POST['id_almacen'] ?? '';
$codigo = $_POST['codigo'] ?? '';
$nombre = $_POST['nombre'] ?? '';
$activo = $_POST['activo'] ?? 1;

if($id == ''){
    $sql = $conexion->prepare("INSERT INTO almacen (codigo, nombre, activo) VALUES (?, ?, ?)");
    $sql->bind_param("ssi", $codigo, $nombre, $activo);
} else {
    $sql = $conexion->prepare("UPDATE almacen SET codigo=?, nombre=?, activo=? WHERE id_almacen=?");
    $sql->bind_param("ssii", $codigo, $nombre, $activo, $id);
}

if($sql->execute()) echo "ok";
else echo "Error: " . $conexion->error;
