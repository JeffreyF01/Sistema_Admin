<?php
require "conexion.php";

$sku = $_POST['sku'];
$nombre = $_POST['nombre'];
$departamento_id = $_POST['departamento_id'];
$grupo_id = $_POST['grupo_id'];
$unidad = $_POST['unidad'];
$precio_venta = $_POST['precio_venta'];
$costo = $_POST['costo'];
$stock = $_POST['stock'];
$stock_min = $_POST['stock_min'];
$ubicacion_id = $_POST['ubicacion_id'];
$activo = $_POST['activo'];

$sql = $conexion->prepare("
INSERT INTO producto 
(sku, nombre, departamento_id, grupo_id, unidad, precio_venta, costo, stock, stock_min, ubicacion_id, activo)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

$sql->bind_param("ssiisddiiii", 
    $sku, 
    $nombre, 
    $departamento_id, 
    $grupo_id, 
    $unidad, 
    $precio_venta, 
    $costo, 
    $stock, 
    $stock_min, 
    $ubicacion_id, 
    $activo
);


if($sql->execute()){
  echo "ok";
}else{
  echo "Error: " . $conexion->error;
}
