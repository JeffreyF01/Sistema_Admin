<?php
require "conexion.php";

$sql = "SELECT * FROM moneda";
$result = mysqli_query($conexion, $sql);

while($row = mysqli_fetch_assoc($result)){
    $estado = $row['activo'] 
        ? "<span style='background-color:#d1e7dd;color:#0f5132;padding:6px 12px;border-radius:20px;font-weight:500;'>
             <i class='fas fa-circle me-1' style='font-size:8px;color:#198754;'></i>Activo
           </span>"
        : "<span style='background-color:#f8d7da;color:#842029;padding:6px 12px;border-radius:20px;font-weight:500;'>
             <i class='fas fa-circle me-1' style='font-size:8px;color:#dc3545;'></i>Inactivo
           </span>";
    $base = $row['es_base'] == 1 ? "Sí" : "No";
    $tasa = number_format($row['tasa_cambio'], 2); // ✅ Solo 2 decimales
    echo "
    <tr>
        <td>{$row['id_monedas']}</td>
        <td>{$row['codigo']}</td>
        <td>{$row['nombre']}</td>
        <td>{$row['simbolo']}</td>
        <td>{$tasa}</td>
        <td>{$base}</td>
        <td>{$estado}</td>
        <td>
            <button class='btn btn-sm btn-warning' onclick=\"editar(
                '{$row['id_monedas']}',
                '{$row['codigo']}',
                '{$row['nombre']}',
                '{$row['simbolo']}',
                '{$row['tasa_cambio']}',
                '{$row['es_base']}',
                '{$row['activo']}'
            )\"><i class='fas fa-edit'></i></button>
            <button class='btn btn-sm btn-danger' onclick=\"eliminar({$row['id_monedas']})\">
                <i class='fas fa-trash-alt'></i>
            </button>
        </td>
    </tr>
    ";
}
?>
