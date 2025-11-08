<?php
require "conexion.php";

$sql = $conexion->query("SELECT * FROM tipo_movimiento");

while($row = $sql->fetch_assoc()){
  $id = $row['id_tipos_movimiento'];
  echo "
  <tr>
    <td>{$row['id_tipos_movimiento']}</td>
    <td>{$row['nombre']}</td>
    <td>{$row['clase']}</td>
    <td>{$row['descripcion']}</td>
    <td>" . ($row['activo'] ? 'Activo' : 'Inactivo') . "</td>
    <td>
      <button class='btn btn-warning btn-sm' onclick='editarMovimiento($id, " . json_encode($row['nombre']) . ", " . json_encode($row['clase']) . ", " . json_encode($row['descripcion']) . ", {$row['activo']})'>✏ Editar</button>
    </td>
  </tr>
  ";
}
?>
