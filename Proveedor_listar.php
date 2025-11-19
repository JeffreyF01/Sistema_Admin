<?php
require "conexion.php";

$sql = "SELECT id_proveedores, nombre, rnc, email, telefono, direccion, activo 
        FROM proveedor ORDER BY id_proveedores DESC";
$res = $conexion->query($sql);

if(!$res){
    echo "<tr><td colspan='8'>Error: ".$conexion->error."</td></tr>";
    exit();
}

while($row = $res->fetch_assoc()){
    $id = (int)$row['id_proveedores'];

    $estado = $row['activo']
        ? "<span class='badge bg-success p-2 px-3'>Activo</span>"
        : "<span class='badge bg-danger p-2 px-3'>Inactivo</span>";

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
