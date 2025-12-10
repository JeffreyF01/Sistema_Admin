<?php
include("conexion.php");

$result = $conexion->query("
SELECT u.id_ubicaciones, u.codigo, u.nombre, u.id_almacen, u.activo, a.nombre AS almacen_nombre
FROM ubicacion u
LEFT JOIN almacen a ON u.id_almacen = a.id_almacen
ORDER BY u.id_ubicaciones ASC
");

while($row = $result->fetch_assoc()){
    $estado = $row['activo'] 
        ? "<span style='background-color:#d1e7dd;color:#0f5132;padding:4px 8px;border-radius:12px;font-weight:500;font-size:12px;white-space:nowrap;'>
             <i class='fas fa-circle me-1' style='font-size:6px;color:#198754;'></i>Activo
           </span>"
        : "<span style='background-color:#f8d7da;color:#842029;padding:4px 8px;border-radius:12px;font-weight:500;font-size:12px;white-space:nowrap;'>
             <i class='fas fa-circle me-1' style='font-size:6px;color:#dc3545;'></i>Inactivo
           </span>";
?>
<tr>
    <td><?= $row['id_ubicaciones'] ?></td>
    <td><?= htmlspecialchars($row['codigo']) ?></td>
    <td><?= htmlspecialchars($row['nombre']) ?></td>
    <td><?= htmlspecialchars($row['almacen_nombre']) ?></td>
    <td><?= $estado ?></td>
    <td>
        <div class="d-flex gap-2">
            <button class="btn btn-warning btn-sm btn-editar"
                data-id="<?= $row['id_ubicaciones'] ?>"
                data-codigo="<?= htmlspecialchars($row['codigo']) ?>"
                data-nombre="<?= htmlspecialchars($row['nombre']) ?>"
                data-almacen="<?= $row['id_almacen'] ?>"
                data-activo="<?= $row['activo'] ?>">
                <i class="fas fa-edit"></i>
            </button>

            <button class="btn btn-danger btn-sm" onclick="eliminar(<?= $row['id_ubicaciones'] ?>)">
                <i class="fas fa-trash-alt"></i>
            </button>
        </div>
    </td>
</tr>
<?php } ?>
