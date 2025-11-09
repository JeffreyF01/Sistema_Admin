<?php
require "conexion.php";

$accion = $_POST['accion'] ?? '';

switch($accion) {
    case 'agregar':
        agregarProducto($conexion);
        break;
    case 'editar':
        editarProducto($conexion);
        break;
    case 'eliminar':
        eliminarProducto($conexion);
        break;
    default:
        echo "Acción no válida";
        break;
}

// ================= FUNCIONES ================= //

function agregarProducto($conexion) {
    $sku = $_POST['sku'] ?? '';
    $nombre = $_POST['nombre'] ?? '';
    $departamento_id = $_POST['departamento_id'] ?? 0;
    $grupo_id = $_POST['grupo_id'] ?? 0;
    $unidad = $_POST['unidad'] ?? '';
    $precio_venta = $_POST['precio_venta'] ?? 0;
    $costo = $_POST['costo'] ?? 0;
    $stock = $_POST['stock'] ?? 0;
    $stock_min = $_POST['stock_min'] ?? 0;
    $ubicacion_id = $_POST['ubicacion_id'] ?? 0;
    $activo = $_POST['activo'] ?? 1;

    $sql = $conexion->prepare("
        INSERT INTO producto 
        (sku, nombre, departamento_id, grupo_id, unidad, precio_venta, costo, stock, stock_min, ubicacion_id, activo)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $sql->bind_param("ssiisddiiii",
        $sku, $nombre, $departamento_id, $grupo_id, $unidad,
        $precio_venta, $costo, $stock, $stock_min, $ubicacion_id, $activo
    );

    echo $sql->execute() ? "ok" : "Error: " . $conexion->error;
}

function editarProducto($conexion) {
    $id = $_POST['id_productos'] ?? 0;
    $sku = $_POST['sku'] ?? '';
    $nombre = $_POST['nombre'] ?? '';
    $departamento_id = $_POST['departamento_id'] ?? 0;
    $grupo_id = $_POST['grupo_id'] ?? 0;
    $unidad = $_POST['unidad'] ?? '';
    $precio_venta = $_POST['precio_venta'] ?? 0;
    $costo = $_POST['costo'] ?? 0;
    $stock = $_POST['stock'] ?? 0;
    $stock_min = $_POST['stock_min'] ?? 0;
    $ubicacion_id = $_POST['ubicacion_id'] ?? 0;
    $activo = $_POST['activo'] ?? 1;

    $sql = $conexion->prepare("
        UPDATE producto SET 
        sku=?, nombre=?, departamento_id=?, grupo_id=?, unidad=?, 
        precio_venta=?, costo=?, stock=?, stock_min=?, ubicacion_id=?, activo=? 
        WHERE id_productos=?
    ");
    $sql->bind_param("ssiisddiiiis", 
        $sku, $nombre, $departamento_id, $grupo_id, $unidad, 
        $precio_venta, $costo, $stock, $stock_min, $ubicacion_id, $activo, $id
    );

    echo $sql->execute() ? "ok" : "Error: " . $conexion->error;
}

function eliminarProducto($conexion) {
    $id = $_POST['id_productos'] ?? 0;

    if(!$id){
        echo "Error: ID de producto no válido";
        return;
    }

    $sql = $conexion->prepare("DELETE FROM producto WHERE id_productos = ?");
    $sql->bind_param("i", $id);
    echo $sql->execute() ? "ok" : "Error: " . $conexion->error;
}
?>
