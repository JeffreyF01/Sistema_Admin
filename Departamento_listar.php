<?php
include("conexion.php");

$result = $conexion->query("SELECT * FROM departamento ORDER BY id_departamentos DESC");

while($row = $result->fetch_assoc()){ ?>
<tr>
  <td><?= $row['id_departamentos'] ?></td>
  <td><?= $row['nombre'] ?></td>
  <td><?= $row['activo'] == 1 ? '✅ Activo' : '❌ Inactivo' ?></td>
  <td>
    <button class="btn btn-warning btn-sm" 
      onclick="editar('<?= $row['id_departamentos'] ?>','<?= $row['nombre'] ?>','<?= $row['activo'] ?>')">
      ✏ Editar
    </button>
  </td>
</tr>
<?php } ?>
