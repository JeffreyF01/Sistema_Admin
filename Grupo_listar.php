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
  $nombre = addslashes($row['nombre']);
  $activo = $row['activo'];

  $estado = $activo 
    ? "<span style='background-color:#d1e7dd;color:#0f5132;padding:4px 8px;border-radius:12px;font-weight:500;font-size:12px;white-space:nowrap;'>
         <i class='fas fa-circle me-1' style='font-size:6px;color:#198754;'></i>Activo
       </span>"
    : "<span style='background-color:#f8d7da;color:#842029;padding:4px 8px;border-radius:12px;font-weight:500;font-size:12px;white-space:nowrap;'>
         <i class='fas fa-circle me-1' style='font-size:6px;color:#dc3545;'></i>Inactivo
       </span>";

  echo "
  <tr>
    <td>{$row['id_grupos']}</td>
    <td>{$row['depto']}</td>
    <td>{$row['nombre']}</td>
    <td>{$estado}</td>
    <td class='text-center'>
      <div class='d-inline-flex'>
        <button class='btn btn-warning btn-sm me-1' 
          onclick='editar($id, $depto_id, " . json_encode($row['nombre']) . ", $activo)'>
          <i class='fas fa-edit'></i>
        </button>
        <button class='btn btn-danger btn-sm' 
          onclick='eliminar($id)'>
          <i class='fas fa-trash'></i>
        </button>
      </div>
    </td>
  </tr>
  ";
}
?>
