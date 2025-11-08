<?php
require "conexion.php";

$sql = "
SELECT g.*, d.nombre AS depto 
FROM grupo g
JOIN departamento d ON g.departamento_id = d.id_departamentos
";

$res = $conexion->query($sql);

while($row = $res->fetch_assoc()){
  $id = $row['id_grupos'];
  $depto_id = $row['departamento_id'];
  $codigo_nombre = addslashes($row['nombre']);
  echo "
  <tr>
    <td>{$row['id_grupos']}</td>
    <td>{$row['depto']}</td>
    <td>{$row['nombre']}</td>
    <td>" . ($row['activo'] ? 'Activo' : 'Inactivo') . "</td>
    <td>
      <button class='btn btn-warning btn-sm' onclick='editar($id, $depto_id, " . json_encode($row['nombre']) . ", {$row['activo']})'>✏ Editar</button>
    </td>
  </tr>
  ";
}
?>
