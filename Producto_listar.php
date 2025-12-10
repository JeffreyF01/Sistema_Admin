<?php
include("conexion.php");

$result = $conexion->query("
SELECT 
    p.id_productos, p.sku, p.nombre, p.stock, p.activo,
    p.departamento_id, p.grupo_id, p.unidad, p.precio_venta, p.costo, 
    p.stock_min, p.ubicacion_id, d.nombre AS departamento, g.nombre AS grupo
FROM producto p
LEFT JOIN departamento d ON p.departamento_id = d.id_departamentos
LEFT JOIN grupo g ON p.grupo_id = g.id_grupos
ORDER BY p.id_productos ASC
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
    <td><?= $row['id_productos'] ?></td>
    <td><?= htmlspecialchars($row['sku']) ?></td>
    <td><?= htmlspecialchars($row['nombre']) ?></td>
    <td><?= htmlspecialchars($row['departamento']) ?></td>
    <td><?= htmlspecialchars($row['grupo']) ?></td>
    <td><?= number_format($row['stock'], 0) ?></td>
    <td><?= $estado ?></td>
    <td>
        <div class="d-flex gap-2 justify-content-center">
            <button class="btn btn-warning btn-sm btn-editar"
                data-id="<?= $row['id_productos'] ?>"
                data-sku="<?= htmlspecialchars($row['sku']) ?>"
                data-nombre="<?= htmlspecialchars($row['nombre']) ?>"
                data-departamento="<?= $row['departamento_id'] ?>"
                data-grupo="<?= $row['grupo_id'] ?>"
                data-unidad="<?= htmlspecialchars($row['unidad']) ?>"
                data-precio="<?= $row['precio_venta'] ?>"
                data-costo="<?= $row['costo'] ?>"
                data-stock="<?= $row['stock'] ?>"
                data-stockmin="<?= $row['stock_min'] ?>"
                data-ubicacion="<?= $row['ubicacion_id'] ?>"
                data-activo="<?= $row['activo'] ?>">
                <i class="fas fa-edit"></i>
            </button>

            <button class="btn btn-danger btn-sm" onclick="eliminar(<?= $row['id_productos'] ?>)">
                <i class="fas fa-trash-alt"></i>
            </button>
        </div>
    </td>
</tr>
<?php } ?>
