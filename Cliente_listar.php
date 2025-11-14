<?php
require "conexion.php";

$sql = "SELECT id_clientes, nombre, doc_identidad, email, telefono, direccion, activo FROM cliente ORDER BY id_clientes DESC";
$res = $conexion->query($sql);

if(!$res){
    echo "<tr><td colspan='8'>Error: " . $conexion->error . "</td></tr>";
    exit();
}

while($row = $res->fetch_assoc()){
    $id = (int)$row['id_clientes'];
    $estado = $row['activo'] 
        ? "<span style='background-color:#d1e7dd;color:#0f5132;padding:6px 12px;border-radius:20px;font-weight:500;'><i class='fas fa-circle me-1' style='font-size:8px;color:#198754;'></i>Activo</span>"
        : "<span style='background-color:#f8d7da;color:#842029;padding:6px 12px;border-radius:20px;font-weight:500;'><i class='fas fa-circle me-1' style='font-size:8px;color:#dc3545;'></i>Inactivo</span>";

    // Escapar salidas
    $nombre = htmlspecialchars($row['nombre']);
    $doc = htmlspecialchars($row['doc_identidad']);
    $email = htmlspecialchars($row['email']);
    $telefono = htmlspecialchars($row['telefono']);
    $direccion = htmlspecialchars($row['direccion']);

    echo "
    <tr>
        <td>{$id}</td>
        <td>{$nombre}</td>
        <td>{$doc}</td>
        <td>{$email}</td>
        <td>{$telefono}</td>
        <td>{$direccion}</td>
        <td>{$estado}</td>
        <td class='text-center'>
            <div class='d-inline-flex'>
                <button class='btn btn-warning btn-sm me-1' onclick='editar({$id})'>
                    <i class='fas fa-edit'></i>
                </button>
                <button class='btn btn-danger btn-sm' onclick='eliminar({$id})'>
                    <i class='fas fa-trash'></i>
                </button>
            </div>
        </td>
    </tr>
    ";
}
?>
