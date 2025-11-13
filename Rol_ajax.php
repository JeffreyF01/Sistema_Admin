<?php
require "conexion.php";

if(isset($_POST['eliminar'])){
    $id = $_POST['id_roles'];
    $sql = "DELETE FROM role WHERE id_roles = $id"; // Asegúrate que tu tabla se llama "role"
    if(mysqli_query($conexion, $sql)){
        echo "ok";
    } else {
        echo mysqli_error($conexion);
    }
    exit();
}

$id = $_POST['id_roles'] ?? '';
$nombre = $_POST['nombre'];
$descripcion = $_POST['descripcion'];
$activo = $_POST['activo'];

if($id == ''){
    $sql = "INSERT INTO role (nombre, descripcion, activo) 
            VALUES ('$nombre', '$descripcion', '$activo')";
} else {
    $sql = "UPDATE role SET 
                nombre='$nombre', 
                descripcion='$descripcion', 
                activo='$activo'
            WHERE id_roles='$id'";
}

if(mysqli_query($conexion, $sql)){
    echo "ok";
} else {
    echo mysqli_error($conexion);
}
?>
