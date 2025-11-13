<?php
require "conexion.php";

$sql = "SELECT u.*, r.nombre AS rol FROM usuario u
        LEFT JOIN role r ON u.id_rol = r.id_roles
        ORDER BY u.id_usuarios DESC";
$result = $conexion->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $estado = $row['activo'] 
        ? "<span style='background-color:#d1e7dd;color:#0f5132;padding:6px 12px;border-radius:20px;font-weight:500;'>
             <i class='fas fa-circle me-1' style='font-size:8px;color:#198754;'></i>Activo
           </span>"
        : "<span style='background-color:#f8d7da;color:#842029;padding:6px 12px;border-radius:20px;font-weight:500;'>
             <i class='fas fa-circle me-1' style='font-size:8px;color:#dc3545;'></i>Inactivo
           </span>";

        echo "<tr>
                <td>{$row['id_usuarios']}</td>
                <td>{$row['nombre']}</td>
                <td>{$row['usuario']}</td>
                <td>{$row['rol']}</td>
                <td>{$estado}</td>
                <td>
                    <button class='btn btn-warning btn-sm me-1' 
                        onclick=\"editar('{$row['id_usuarios']}', '{$row['nombre']}', '{$row['usuario']}', '{$row['clave']}', '{$row['id_rol']}', '{$row['activo']}')\">
                        <i class='fas fa-edit'></i>
                    </button>
                    <button class='btn btn-danger btn-sm' onclick='eliminar({$row['id_usuarios']})'>
                        <i class='fas fa-trash-alt'></i>
                    </button>
                </td>
              </tr>";
    }
} else {
    echo "<tr><td colspan='6'>No hay usuarios registrados.</td></tr>";
}
?>
