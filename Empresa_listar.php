<?php
require "conexion.php";

$sql = "SELECT e.*, m.nombre AS moneda 
        FROM empresa e 
        LEFT JOIN moneda m ON e.moneda_base_id = m.id_monedas";
$result = mysqli_query($conexion, $sql);

while($row = mysqli_fetch_assoc($result)){
    $logo = $row['logo_url'] ? "<img src='{$row['logo_url']}' style='height:40px;' alt='logo'>" : "—";
    echo "
    <tr>
        <td>{$row['id_empresa']}</td>
        <td>{$row['nombre']}</td>
        <td>{$row['rnc']}</td>
        <td>{$row['telefono']}</td>
        <td>{$row['email']}</td>
        <td>{$row['moneda']}</td>
        <td>{$logo}</td>
        <td>
            <button class='btn btn-sm btn-warning' onclick=\"editar(
                '{$row['id_empresa']}',
                '{$row['nombre']}',
                '{$row['rnc']}',
                '{$row['direccion']}',
                '{$row['telefono']}',
                '{$row['email']}',
                '{$row['moneda_base_id']}',
                '{$row['logo_url']}'
            )\"><i class='fas fa-edit'></i></button>
            <button class='btn btn-sm btn-danger' onclick=\"eliminar({$row['id_empresa']})\">
                <i class='fas fa-trash-alt'></i>
            </button>
        </td>
    </tr>
    ";
}
?>
