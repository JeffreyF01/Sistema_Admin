<?php
include("conexion.php");

$result = $conexion->query("SELECT * FROM departamento ORDER BY id_departamentos ASC");

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $activo = $row['activo'];

        // ✅ Estado con mismo estilo que Grupos
        $estado = $activo
            ? "<span style='background-color:#d1e7dd;color:#0f5132;padding:4px 8px;border-radius:12px;font-weight:500;font-size:12px;white-space:nowrap;'>
                 <i class='fas fa-circle me-1' style='font-size:6px;color:#198754;'></i>Activo
               </span>"
            : "<span style='background-color:#f8d7da;color:#842029;padding:4px 8px;border-radius:12px;font-weight:500;font-size:12px;white-space:nowrap;'>
                 <i class='fas fa-circle me-1' style='font-size:6px;color:#dc3545;'></i>Inactivo
               </span>";
        ?>
        <tr>
            <td><?= $row['id_departamentos'] ?></td>
            <td><?= htmlspecialchars($row['nombre']) ?></td>
            <td><?= $estado ?></td>
            <td>
                <div class="d-flex gap-1">
                    <button class="btn btn-warning btn-sm"
                        onclick="editar('<?= $row['id_departamentos'] ?>','<?= htmlspecialchars($row['nombre'], ENT_QUOTES) ?>','<?= $row['activo'] ?>')">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-danger btn-sm"
                        onclick="eliminarDepartamento('<?= $row['id_departamentos'] ?>')">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
            </td>
        </tr>
        <?php
    }
} else {
    echo "<tr><td colspan='4' class='text-center text-muted'>No hay departamentos registrados</td></tr>";
}
?>
