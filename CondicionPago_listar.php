<?php
require "conexion.php";

$sql = "SELECT * FROM condicion_pago";
$result = mysqli_query($conexion, $sql);

while($row = mysqli_fetch_assoc($result)){
    $activo = $row['activo'] 
        ? "<span style='background-color:#d1e7dd;color:#0f5132;padding:4px 8px;border-radius:12px;font-weight:500;font-size:12px;white-space:nowrap;'>
             <i class='fas fa-circle me-1' style='font-size:6px;color:#198754;'></i>Activo
           </span>"
        : "<span style='background-color:#f8d7da;color:#842029;padding:4px 8px;border-radius:12px;font-weight:500;font-size:12px;white-space:nowrap;'>
             <i class='fas fa-circle me-1' style='font-size:6px;color:#dc3545;'></i>Inactivo
           </span>";

    echo "
    <tr>
        <td>{$row['id_condiciones_pago']}</td>
        <td>{$row['nombre']}</td>
        <td>{$row['dias_plazo']} días</td>
        <td>{$activo}</td>
        <td>
            <button class='btn btn-sm btn-warning' onclick=\"editar(
                '{$row['id_condiciones_pago']}',
                '{$row['nombre']}',
                '{$row['dias_plazo']}',
                '{$row['activo']}'
            )\"><i class='fas fa-edit'></i></button>
            <button class='btn btn-sm btn-danger' onclick=\"eliminar({$row['id_condiciones_pago']})\">
                <i class='fas fa-trash-alt'></i>
            </button>
        </td>
    </tr>
    ";
}
?>
