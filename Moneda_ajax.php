<?php
require "conexion.php";

if(isset($_POST['eliminar'])){
    $id = $_POST['id_monedas'];
    $sql = "DELETE FROM moneda WHERE id_monedas = $id"; // Si tu tabla se llama diferente, cambia aquí
    if(mysqli_query($conexion, $sql)){
        echo "ok";
    } else {
        echo mysqli_error($conexion);
    }
    exit();
}

$id = $_POST['id_monedas'] ?? '';
$codigo = $_POST['codigo'];
$nombre = $_POST['nombre'];
$simbolo = $_POST['simbolo'];
$tasa = $_POST['tasa_cambio'];
$es_base = $_POST['es_base'];
$activo = $_POST['activo'];

if($id == ''){
    $sql = "INSERT INTO moneda (codigo, nombre, simbolo, tasa_cambio, es_base, activo) 
            VALUES ('$codigo', '$nombre', '$simbolo', '$tasa', '$es_base', '$activo')";
} else {
    $sql = "UPDATE moneda SET 
                codigo='$codigo', nombre='$nombre', simbolo='$simbolo', 
                tasa_cambio='$tasa', es_base='$es_base', activo='$activo'
            WHERE id_monedas='$id'";
}

if(mysqli_query($conexion, $sql)){
    echo "ok";
} else {
    echo mysqli_error($conexion);
}
?>
