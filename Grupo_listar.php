<?php
require "conexion.php";

$sql = "
SELECT g.*, d.nombre AS depto 
FROM grupo g
JOIN departamento d ON g.departamento_id = d.id_departamentos
";

$res = $conexion->query($sql);

while($row = $res->fetch_assoc()){
  echo "
  <tr>
    <td>{$row['id_grupos']}</td>
    <td>{$row['depto']}</td>
    <td>{$row['nombre']}</td>
    <td>" . ($row['activo'] ? 'Activo' : 'Inactivo') . "</td>
  </tr>
  ";
}
?>
