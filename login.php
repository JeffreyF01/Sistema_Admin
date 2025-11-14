<?php
// login.php
session_start();
require_once "conexion.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = $_POST['usuario'] ?? '';
    $password = $_POST['password'] ?? '';

    // Preparada para evitar inyección
    $stmt = $conexion->prepare("SELECT * FROM usuario WHERE usuario = ? LIMIT 1");
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado && $resultado->num_rows > 0) {
        $row = $resultado->fetch_assoc();

        // Nota: asumes claves en texto plano. Si usas password_hash -> password_verify aquí.
        if ($password === $row['clave'] && (int)$row['activo'] === 1) {
            // Mantener compatibilidad: $_SESSION['usuario'] como string
            $_SESSION['usuario'] = $row['usuario'];

            // Info adicional (opcionalmente usada)
            $_SESSION['user_info'] = [
                'id_usuarios' => $row['id_usuarios'],
                'nombre'      => $row['nombre'],
                'id_rol'      => $row['id_rol'],
                'activo'      => $row['activo']
            ];

            // Guardar permisos en un array asociativo (0/1)
            $permisos_lista = [
                'inv_productos','inv_almacenes','inv_ubicaciones','inv_departamentos','inv_grupos',
                'inv_cotizaciones','inv_compras','inv_movimientos','inv_devoluciones','inv_facturacion',
                'inv_consultas','inv_reportes','exc_clientes','exc_cobros',
                'exp_proveedores','exp_pagos','conf_usuario','conf_roles','conf_empresa',
                'conf_moneda','conf_condicion'
            ];

            $permisos = [];
            foreach ($permisos_lista as $p) {
                // cast a int para asegurar 0/1
                $permisos[$p] = isset($row[$p]) ? (int)$row[$p] : 0;
            }
            $_SESSION['permisos'] = $permisos;

            header("Location: dashboard.php");
            exit();
        } else {
            echo "<script>alert('Usuario o clave incorrecta / usuario inactivo'); window.location='index.html';</script>";
        }
    } else {
        echo "<script>alert('Usuario no encontrado'); window.location='index.html';</script>";
    }

    $stmt->close();
    $conexion->close();
}
?>
