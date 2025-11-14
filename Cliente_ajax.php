<?php
require "conexion.php";

// Eliminar
if(isset($_POST['eliminar']) && isset($_POST['id_clientes'])){
    $id = (int)$_POST['id_clientes'];
    $stmt = $conexion->prepare("DELETE FROM cliente WHERE id_clientes = ?");
    $stmt->bind_param("i", $id);
    if($stmt->execute()){
        echo "ok";
    } else {
        echo $conexion->error;
    }
    $stmt->close();
    exit();
}

// Obtener un registro (para editar)
if(isset($_GET['obtener'])){
    $id = (int)$_GET['obtener'];
    $stmt = $conexion->prepare("SELECT id_clientes, nombre, doc_identidad, email, telefono, direccion, activo FROM cliente WHERE id_clientes = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    if($res && $res->num_rows > 0){
        $row = $res->fetch_assoc();
        echo json_encode($row);
    } else {
        echo json_encode(['error' => 'Cliente no encontrado']);
    }
    $stmt->close();
    exit();
}

// Insertar / Actualizar
$id = isset($_POST['id_clientes']) && $_POST['id_clientes'] !== '' ? (int)$_POST['id_clientes'] : null;
$nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
$doc = isset($_POST['doc_identidad']) ? trim($_POST['doc_identidad']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$telefono = isset($_POST['telefono']) ? trim($_POST['telefono']) : '';
$direccion = isset($_POST['direccion']) ? trim($_POST['direccion']) : '';
$activo = isset($_POST['activo']) ? (int)$_POST['activo'] : 0;

// Validaciones básicas
if($nombre === ''){
    echo "El nombre es obligatorio";
    exit();
}

if($id === null){
    // Insert
    $stmt = $conexion->prepare("INSERT INTO cliente (nombre, doc_identidad, email, telefono, direccion, activo) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssi", $nombre, $doc, $email, $telefono, $direccion, $activo);
    if($stmt->execute()){
        echo "ok";
    } else {
        echo $conexion->error;
    }
    $stmt->close();
    exit();
} else {
    // Update
    $stmt = $conexion->prepare("UPDATE cliente SET nombre = ?, doc_identidad = ?, email = ?, telefono = ?, direccion = ?, activo = ? WHERE id_clientes = ?");
    $stmt->bind_param("sssssii", $nombre, $doc, $email, $telefono, $direccion, $activo, $id);
    if($stmt->execute()){
        echo "ok";
    } else {
        echo $conexion->error;
    }
    $stmt->close();
    exit();
}
?>
