<?php
require "conexion.php";

if(isset($_POST['eliminar'])){
    $id = $_POST['id_empresa'];
    $sql = "DELETE FROM empresa WHERE id_empresa = $id";
    if(mysqli_query($conexion, $sql)){
        echo "ok";
    } else {
        echo mysqli_error($conexion);
    }
    exit();
}

$id = $_POST['id_empresa'] ?? '';
$nombre = $_POST['nombre'];
$rnc = $_POST['rnc'];
$direccion = $_POST['direccion'];
$telefono = $_POST['telefono'];
$email = $_POST['email'];
$moneda_base_id = $_POST['moneda_base_id'];
$logo_url = '';

if(isset($_FILES['logo_url']) && $_FILES['logo_url']['error'] == 0){
    $ruta = "uploads/logos/";
    if(!is_dir($ruta)) mkdir($ruta, 0777, true);
    $nombreArchivo = uniqid() . "_" . basename($_FILES['logo_url']['name']);
    $rutaCompleta = $ruta . $nombreArchivo;
    move_uploaded_file($_FILES['logo_url']['tmp_name'], $rutaCompleta);
    $logo_url = $rutaCompleta;
}

if($id == ''){
    $sql = "INSERT INTO empresa (nombre, rnc, direccion, telefono, email, logo_url, moneda_base_id) 
            VALUES ('$nombre', '$rnc', '$direccion', '$telefono', '$email', '$logo_url', '$moneda_base_id')";
} else {
    if($logo_url != ''){
        $sql = "UPDATE empresa SET 
                    nombre='$nombre', 
                    rnc='$rnc', 
                    direccion='$direccion', 
                    telefono='$telefono', 
                    email='$email',
                    logo_url='$logo_url',
                    moneda_base_id='$moneda_base_id'
                WHERE id_empresa='$id'";
    } else {
        $sql = "UPDATE empresa SET 
                    nombre='$nombre', 
                    rnc='$rnc', 
                    direccion='$direccion', 
                    telefono='$telefono', 
                    email='$email',
                    moneda_base_id='$moneda_base_id'
                WHERE id_empresa='$id'";
    }
}

if(mysqli_query($conexion, $sql)){
    echo "ok";
} else {
    echo mysqli_error($conn);
}
?>
