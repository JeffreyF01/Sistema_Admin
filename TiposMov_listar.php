<?php
require "conexion.php";

$sql = $conexion->query("SELECT * FROM tipo_movimiento");

while($row = $sql->fetch_assoc()){
  echo "
  <tr>
    <td>{$row['id_tipos_movimiento']}</td>
    <td>{$row['nombre']}</td>
    <td>{$row['clase']}</td>
    <td>{$row['descripcion']}</td>
    <td>" . ($row['activo'] ? 'Activo' : 'Inactivo') . "</td>
  </tr>
  ";
}
?>
