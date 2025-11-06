<?php
session_start();
require_once "conexion.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $usuario = $_POST['usuario'];
  $password = $_POST['password'];

  $stmt = $conexion->prepare("SELECT id_usuarios, clave FROM usuario WHERE usuario = ?");
  $stmt->bind_param("s", $usuario);
  $stmt->execute();
  $resultado = $stmt->get_result();

  if ($resultado->num_rows > 0) {
    $row = $resultado->fetch_assoc();

    if ($password == $row['clave']) {
      $_SESSION['usuario'] = $usuario;
      header("Location: dashboard.php");
      exit();
    } else {
      echo "<script>alert('Contraseña incorrecta'); window.location='index.html';</script>";
    }
  } else {
    echo "<script>alert('Usuario no encontrado'); window.location='index.html';</script>";
  }

  $stmt->close();
  $conexion->close();
}
?>