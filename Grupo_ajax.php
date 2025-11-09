<?php
require "conexion.php";

// Verificamos si se está eliminando un grupo
// 🗑 ELIMINAR GRUPO
if (isset($_POST['accion']) && $_POST['accion'] === 'eliminar') {
    $id = $_POST['id_grupos'];

    // Verificar si hay productos asociados
    $checkProductos = $conexion->prepare("SELECT COUNT(*) AS total FROM producto WHERE grupo_id = ?");
    $checkProductos->bind_param("i", $id);
    $checkProductos->execute();
    $res = $checkProductos->get_result()->fetch_assoc();

    if ($res['total'] > 0) {
        echo "No se puede eliminar este grupo porque está asociado a productos.";
        exit;
    }

    // Si no tiene productos asociados, eliminar
    $stmt = $conexion->prepare("DELETE FROM grupo WHERE id_grupos = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo "ok";
    } else {
        echo "Error al eliminar: " . $conexion->error;
    }
    exit;
}


// Si no se está eliminando, procesamos guardar/editar
$id = $_POST['id_grupos'] ?? "";
$departamento_id = $_POST['departamento_id'];
$nombre = $_POST['nombre'];
$activo = $_POST['activo'];

if ($id == "") {
    // Insertar nuevo grupo
    $stmt = $conexion->prepare("INSERT INTO grupo (departamento_id, nombre, activo) VALUES (?, ?, ?)");
    $stmt->bind_param("isi", $departamento_id, $nombre, $activo);
} else {
    // Actualizar grupo existente
    $stmt = $conexion->prepare("UPDATE grupo SET departamento_id=?, nombre=?, activo=? WHERE id_grupos=?");
    $stmt->bind_param("isii", $departamento_id, $nombre, $activo, $id);
}

if ($stmt->execute()) {
    echo "ok";
} else {
    echo "Error al guardar: " . $conexion->error;
}
?>
