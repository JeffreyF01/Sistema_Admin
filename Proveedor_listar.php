<?php
require "conexion.php";

$sql = "SELECT id_proveedores, nombre, rnc, email, telefono, direccion, activo 
        FROM proveedor ORDER BY id_proveedores ASC";
$res = $conexion->query($sql);

if(!$res){
    echo "<tr><td colspan='8'>Error: ".$conexion->error."</td></tr>";
    exit();
}

while($row = $res->fetch_assoc()){
    $id = (int)$row['id_proveedores'];

    $estado = $row['activo'] 
        ? "<span style='background-color:#d1e7dd;color:#0f5132;padding:4px 8px;border-radius:12px;font-weight:500;font-size:12px;white-space:nowrap;'><i class='fas fa-circle me-1' style='font-size:6px;color:#198754;'></i>Activo</span>"
        : "<span style='background-color:#f8d7da;color:#842029;padding:4px 8px;border-radius:12px;font-weight:500;font-size:12px;white-space:nowrap;'><i class='fas fa-circle me-1' style='font-size:6px;color:#dc3545;'></i>Inactivo</span>";

    echo "
    <tr>
        <td>{$id}</td>
        <td>".htmlspecialchars($row['nombre'])."</td>
        <td>".htmlspecialchars($row['rnc'])."</td>
        <td>".htmlspecialchars($row['email'])."</td>
        <td>".htmlspecialchars($row['telefono'])."</td>
        <td>".htmlspecialchars($row['direccion'])."</td>
        <td>{$estado}</td>

        <td>
            <button class='btn btn-warning btn-sm me-1' onclick='editar({$id})'>
                <i class='fas fa-edit'></i>
            </button>

            <button class='btn btn-danger btn-sm' onclick='eliminar({$id})'>
                <i class='fas fa-trash'></i>
            </button>
        </td>
    </tr>";
}
?>
