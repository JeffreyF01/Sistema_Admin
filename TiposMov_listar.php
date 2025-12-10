<?php
require "conexion.php";

$sql = "SELECT * FROM tipo_movimiento";
$res = $conexion->query($sql);

while($row = $res->fetch_assoc()){
    $id = $row['id_tipos_movimiento'];
    $nombre = addslashes($row['nombre']);
    $descripcion = addslashes($row['clase']);
    $tipo = addslashes($row['descripcion']);
    $activo = $row['activo'];

    $estado = $activo 
        ? "<span style='background-color:#d1e7dd;color:#0f5132;padding:4px 8px;border-radius:12px;font-weight:500;font-size:12px;white-space:nowrap;'>
             <i class='fas fa-circle me-1' style='font-size:6px;color:#198754;'></i>Activo
           </span>"
        : "<span style='background-color:#f8d7da;color:#842029;padding:4px 8px;border-radius:12px;font-weight:500;font-size:12px;white-space:nowrap;'>
             <i class='fas fa-circle me-1' style='font-size:6px;color:#dc3545;'></i>Inactivo
           </span>";

    echo "
    <tr>
        <td>{$row['id_tipos_movimiento']}</td>
        <td>{$row['nombre']}</td>
        <td>{$row['clase']}</td>
        <td>{$row['descripcion']}</td>
        <td>{$estado}</td>
        <td class='text-center'>
            <div class='d-inline-flex'>
                <button class='btn btn-warning btn-sm me-1' 
                    onclick='editar($id, " . json_encode($row['nombre']) . ", " . json_encode($row['clase']) . ", " . json_encode($row['descripcion']) . ", $activo)'>
                    <i class=\"fas fa-edit\"></i>
                </button>
                <button class='btn btn-danger btn-sm' onclick='eliminar($id)'>
                    <i class=\"fas fa-trash\"></i>
                </button>
            </div>
        </td>
    </tr>
    ";
}
?>
