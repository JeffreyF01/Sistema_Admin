<?php
require "conexion.php";

// 🟢 Obtener usuario para editar
if (isset($_GET['obtener'])) {
    $id = $_GET['obtener'];
    $res = $conexion->query("SELECT * FROM usuario WHERE id_usuarios = '$id'");
    if($res->num_rows > 0){
        echo json_encode($res->fetch_assoc());
    } else {
        echo json_encode(["error" => "Usuario no encontrado"]);
    }
    exit;
}

// 🗑️ Eliminar usuario
if (isset($_POST['eliminar'])) {
    $id = $_POST['id_usuarios'];
    $sql = "DELETE FROM usuario WHERE id_usuarios = '$id'";
    if ($conexion->query($sql)) {
        echo "ok";
    } else {
        echo "Error al eliminar: " . $conexion->error;
    }
    exit;
}

// 🧩 Insertar o actualizar usuario
$id = $_POST['id_usuarios'] ?? '';
$nombre = $_POST['nombre'];
$usuario = $_POST['usuario'];
$clave = $_POST['clave'];
$id_rol = $_POST['id_rol'];
$activo = $_POST['activo'];

// Permisos (todos 0 o 1)
$campos = [
    'inv_productos','inv_almacenes','inv_ubicaciones','inv_departamentos','inv_grupos',
    'inv_cotizaciones','inv_compras','inv_movimientos','inv_devoluciones','inv_facturacion',
    'inv_consultas','inv_reportes',
    'exc_clientes','exc_cobros','exp_proveedores','exp_pagos',
    'conf_usuario','conf_roles','conf_empresa','conf_moneda','conf_condicion'
];

$valores = [];
foreach ($campos as $c) {
    $valores[$c] = isset($_POST[$c]) ? 1 : 0;
}

if ($id == '') {
    // INSERTAR
    $sql = "INSERT INTO usuario (
                nombre, usuario, clave, id_rol, activo,
                inv_productos, inv_almacenes, inv_ubicaciones, inv_departamentos, inv_grupos,
                inv_cotizaciones, inv_compras, inv_movimientos, inv_devoluciones, inv_facturacion,
                inv_consultas, inv_reportes, exc_clientes, exc_cobros,
                exp_proveedores, exp_pagos, conf_usuario, conf_roles, conf_empresa, conf_moneda, conf_condicion
            ) VALUES (
                '$nombre', '$usuario', '$clave', '$id_rol', '$activo',
                {$valores['inv_productos']}, {$valores['inv_almacenes']}, {$valores['inv_ubicaciones']}, {$valores['inv_departamentos']}, {$valores['inv_grupos']},
                {$valores['inv_cotizaciones']}, {$valores['inv_compras']}, {$valores['inv_movimientos']}, {$valores['inv_devoluciones']}, {$valores['inv_facturacion']},
                {$valores['inv_consultas']}, {$valores['inv_reportes']}, {$valores['exc_clientes']}, {$valores['exc_cobros']},
                {$valores['exp_proveedores']}, {$valores['exp_pagos']}, {$valores['conf_usuario']}, {$valores['conf_roles']}, {$valores['conf_empresa']}, {$valores['conf_moneda']}, {$valores['conf_condicion']}
            )";
} else {
    // ACTUALIZAR
    $sql = "UPDATE usuario SET
                nombre='$nombre',
                usuario='$usuario',
                clave='$clave',
                id_rol='$id_rol',
                activo='$activo',
                inv_productos={$valores['inv_productos']},
                inv_almacenes={$valores['inv_almacenes']},
                inv_ubicaciones={$valores['inv_ubicaciones']},
                inv_departamentos={$valores['inv_departamentos']},
                inv_grupos={$valores['inv_grupos']},
                inv_cotizaciones={$valores['inv_cotizaciones']},
                inv_compras={$valores['inv_compras']},
                inv_movimientos={$valores['inv_movimientos']},
                inv_devoluciones={$valores['inv_devoluciones']},
                inv_facturacion={$valores['inv_facturacion']},
                inv_consultas={$valores['inv_consultas']},
                inv_reportes={$valores['inv_reportes']},
                exc_clientes={$valores['exc_clientes']},
                exc_cobros={$valores['exc_cobros']},
                exp_proveedores={$valores['exp_proveedores']},
                exp_pagos={$valores['exp_pagos']},
                conf_usuario={$valores['conf_usuario']},
                conf_roles={$valores['conf_roles']},
                conf_empresa={$valores['conf_empresa']},
                conf_moneda={$valores['conf_moneda']},
                conf_condicion={$valores['conf_condicion']}
            WHERE id_usuarios='$id'";
}

if ($conexion->query($sql)) {
    echo "ok";
} else {
    echo "Error SQL: " . $conexion->error;
}
?>
