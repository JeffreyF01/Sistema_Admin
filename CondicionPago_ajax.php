<?php
require "conexion.php";

if(isset($_POST['eliminar'])){
    $id = $_POST['id_condiciones_pago'];
    $sql = "DELETE FROM condicion_pago WHERE id_condiciones_pago = $id";
    if(mysqli_query($conexion, $sql)){
        echo "ok";
    } else {
        echo mysqli_error($conexion);
    }
    exit();
}

$id = $_POST['id_condiciones_pago'] ?? '';
$nombre = $_POST['nombre'];
$dias_plazo = $_POST['dias_plazo'];
$activo = $_POST['activo'];

if($id == ''){
    $sql = "INSERT INTO condicion_pago (nombre, dias_plazo, activo) 
            VALUES ('$nombre', '$dias_plazo', '$activo')";
} else {
    $sql = "UPDATE condicion_pago 
            SET nombre='$nombre', dias_plazo='$dias_plazo', activo='$activo' 
            WHERE id_condiciones_pago='$id'";
}

if(mysqli_query($conexion, $sql)){
    echo "ok";
} else {
    echo mysqli_error($conexion);
}
?>
