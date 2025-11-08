<?php
include("conexion.php");

$result = $conexion->query("SELECT u.id_ubicaciones, u.codigo, u.nombre, u.id_almacen, u.activo, a.nombre AS almacen_nombre
    FROM ubicacion u
    LEFT JOIN almacen a ON u.id_almacen = a.id_almacen
    ORDER BY u.id_ubicaciones DESC");

while($row = $result->fetch_assoc()){ ?>
<tr>
  <td><?= $row['id_ubicaciones'] ?></td>
  <td><?= $row['codigo'] ?></td>
  <td><?= $row['nombre'] ?></td>
  <td><?= $row['almacen_nombre'] ?></td>
  <td><?= $row['activo'] == 1 ? '✅ Activo' : '❌ Inactivo' ?></td>
  <td>
    <button class="btn btn-warning btn-sm"
      onclick="editar(<?= $row['id_ubicaciones'] ?>, <?= json_encode($row['codigo']) ?>, <?= json_encode($row['nombre']) ?>, <?= $row['id_almacen'] ?>, <?= $row['activo'] ?>)">
      ✏ Editar
    </button>
  </td>
</tr>
<?php } ?>
