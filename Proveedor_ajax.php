<?php
require "conexion.php";

/* ============================
   ELIMINAR
============================ */
if(isset($_POST['eliminar']) && isset($_POST['id_proveedores'])){
    $id = (int)$_POST['id_proveedores'];
    $stmt = $conexion->prepare("DELETE FROM proveedor WHERE id_proveedores = ?");
    $stmt->bind_param("i", $id);

    echo $stmt->execute() ? "ok" : $conexion->error;
    exit();
}

/* ============================
   OBTENER REGISTRO
============================ */
if(isset($_GET['obtener'])){
    $id = (int)$_GET['obtener'];

    $stmt = $conexion->prepare("SELECT id_proveedores, nombre, rnc, email, telefono, direccion, activo FROM proveedor WHERE id_proveedores = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();

    if($res && $res->num_rows > 0){
        echo json_encode($res->fetch_assoc());
    } else {
        echo json_encode(["error"=>"Proveedor no encontrado"]);
    }
    exit();
}

/* ============================
   INSERTAR / ACTUALIZAR
============================ */
$id = $_POST['id_proveedores'] !== '' ? (int)$_POST['id_proveedores'] : null;
$nombre = trim($_POST['nombre']);
$rnc = trim($_POST['rnc']);
$email = trim($_POST['email']);
$telefono = trim($_POST['telefono']);
$direccion = trim($_POST['direccion']);
$activo = (int)$_POST['activo'];

if($nombre === ""){
    echo "El nombre es obligatorio";
    exit();
}

if($id === null){
    // INSERTAR
    $stmt = $conexion->prepare("
        INSERT INTO proveedor (nombre, rnc, email, telefono, direccion, activo)
        VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssi", $nombre, $rnc, $email, $telefono, $direccion, $activo);

    echo $stmt->execute() ? "ok" : $conexion->error;
    exit();

} else {
    // ACTUALIZAR
    $stmt = $conexion->prepare("
        UPDATE proveedor SET nombre=?, rnc=?, email=?, telefono=?, direccion=?, activo=? 
        WHERE id_proveedores=?");
    $stmt->bind_param("sssssii", $nombre, $rnc, $email, $telefono, $direccion, $activo, $id);

    echo $stmt->execute() ? "ok" : $conexion->error;
    exit();
}
?>
