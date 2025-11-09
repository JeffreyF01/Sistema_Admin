<?php
include("conexion.php");

$result = $conexion->query("SELECT * FROM almacen ORDER BY id_almacen DESC");

while($row = $result->fetch_assoc()){ 
    $estado = $row['activo'] 
        ? "<span style='background-color:#d1e7dd;color:#0f5132;padding:6px 12px;border-radius:20px;font-weight:500;'>
             <i class='fas fa-circle me-1' style='font-size:8px;color:#198754;'></i>Activo
           </span>"
        : "<span style='background-color:#f8d7da;color:#842029;padding:6px 12px;border-radius:20px;font-weight:500;'>
             <i class='fas fa-circle me-1' style='font-size:8px;color:#dc3545;'></i>Inactivo
           </span>";
?>
<tr>
  <td><?= $row['id_almacen'] ?></td>
  <td><?= htmlspecialchars($row['codigo']) ?></td>
  <td><?= htmlspecialchars($row['nombre']) ?></td>
  <td><?= $estado ?></td>
  <td class="text-center">
    <div class="d-flex justify-content-center gap-2">
      <button class="btn btn-warning btn-sm" 
        onclick="editar(
          '<?= $row['id_almacen'] ?>',
          '<?= htmlspecialchars($row['codigo'], ENT_QUOTES) ?>',
          '<?= htmlspecialchars($row['nombre'], ENT_QUOTES) ?>',
          '<?= $row['activo'] ?>'
        )">
        <i class="fas fa-edit"></i>
      </button>
      <button class="btn btn-danger btn-sm" onclick="eliminar(<?= $row['id_almacen'] ?>)">
        <i class="fas fa-trash"></i>
      </button>
    </div>
  </td>
</tr>
<?php } ?>
