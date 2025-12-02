<?php
session_start();
require_once '../conexion.php';
header('Content-Type: application/json');

// validar sesión
if (!isset($_SESSION['usuario'])) {
    echo json_encode(['success' => false, 'message' => 'Sesión inválida']);
    exit;
}

// obtener id usuario
$query_usuario = "SELECT id_usuarios FROM usuario WHERE usuario = ? LIMIT 1";
$stmt_u = $conexion->prepare($query_usuario);
$stmt_u->bind_param("s", $_SESSION['usuario']);
$stmt_u->execute();
$resu = $stmt_u->get_result();
$usuario_row = $resu->fetch_assoc();
$usuario_id = $usuario_row['id_usuarios'] ?? null;

// leer input (json)
$input = file_get_contents("php://input");
$data = json_decode($input, true);

// Manejo correcto del JSON de AJAX
if (!is_array($data) || empty($data)) {
    $data = $_POST;
}

$accion = $data['accion'] ?? '';
error_log("DATA RECIBIDA: " . print_r($data, true));


try {
    switch ($accion) {
        case 'guardar': guardarCompra($conexion, $data, $usuario_id); break;
        case 'listar': listarCompras($conexion); break;
        case 'listar_proveedores': listarProveedores($conexion); break;
        case 'listar_productos': listarProductos($conexion); break;
        case 'listar_condiciones': listarCondiciones($conexion); break;
        case 'generar_numero': generarNumero($conexion); break;
        case 'obtener': obtenerCompra($conexion, intval($data['id'])); break;
        default:
            echo json_encode(['success'=>false,'message'=>'Acción no válida']);
    }
} catch (Exception $e) {
    echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
}

/* =======================================================
                    GUARDAR COMPRA
======================================================= */

function guardarCompra($conexion, $data, $usuario_id) {

    if (!$usuario_id) throw new Exception('Usuario no identificado');

    $numero = $data['numero_documento'] ?? '';
    $proveedor_id = intval($data['proveedor_id'] ?? 0);
    $fecha = !empty($data['fecha']) ? $data['fecha'] : date('Y-m-d');
    $condicion_id = !empty($data['condicion_id']) ? intval($data['condicion_id']) : null;
    $detalle = $data['detalle'] ?? [];

    if (empty($numero) || $proveedor_id <= 0 || empty($detalle)) {
        echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
        return;
    }

    $conexion->begin_transaction();

    try {
        /* ---------------- TOTAL ---------------- */
        $total = 0;
        foreach ($detalle as $it) {
            $total += floatval($it['subtotal']);
        }

        /* ---------------- INSERT COMPRA ---------------- */
        $sql = "INSERT INTO compra 
                (numero_documento, proveedor_id, usuario_id, fecha, total, activo, condicion_id)
                VALUES (?, ?, ?, ?, ?, 1, ?)";

        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("siisdi", 
        $numero, 
        $proveedor_id,
        $usuario_id,
        $fecha,         // STRING (s)
        $total,         // DECIMAL (d)
        $condicion_id
);


        if (!$stmt->execute()) throw new Exception("Error insert compra: " . $stmt->error);

        $compra_id = $conexion->insert_id;

        /* ---------------- INSERT DETALLE ---------------- */
        $stmtDet = $conexion->prepare(
            "INSERT INTO compra_detalle (compra_id, producto_id, cantidad, costo_unitario, subtotal, activo)
             VALUES (?, ?, ?, ?, ?, 1)"
        );

        /* ---------------- UPDATE STOCK + COSTO PROMEDIO ---------------- */
        $stmtStock = $conexion->prepare(
            "UPDATE producto 
             SET 
                costo = ((costo * stock) + (? * ?)) / (stock + ?),
                stock = stock + ?
             WHERE id_productos = ?"
        );

        foreach ($detalle as $it) {

            $pid = intval($it['producto_id']);
            $cant = floatval($it['cantidad']);
            $costo = floatval($it['costo_unitario']);
            $subt = floatval($it['subtotal']);

            // detalle
            $stmtDet->bind_param("iiddd", $compra_id, $pid, $cant, $costo, $subt);
            if (!$stmtDet->execute()) 
                throw new Exception("Error insert detalle: " . $stmtDet->error);

            // stock + costo
            $stmtStock->bind_param("ddidi", $costo, $cant, $cant, $cant, $pid);
            if (!$stmtStock->execute()) 
                throw new Exception("Error actualizando stock/costo: " . $stmtStock->error);
        }

        /* ---------------- CXP ---------------- */
        /* ============================================================
            CUENTAS POR PAGAR (SOLO SI ES CRÉDITO)
============================================================ */
$dias_plazo = 0;
$fecha_venc = null;

// Buscar días de plazo de la condición
if ($condicion_id) {
    $stmtCond = $conexion->prepare("SELECT dias_plazo FROM condicion_pago WHERE id_condiciones_pago = ?");
    $stmtCond->bind_param("i", $condicion_id);
    $stmtCond->execute();
    $cond = $stmtCond->get_result()->fetch_assoc();

    $dias_plazo = intval($cond['dias_plazo'] ?? 0);
    $fecha_venc = ($dias_plazo > 0)
        ? date("Y-m-d", strtotime("$fecha + $dias_plazo days"))
        : $fecha;
}

/* SOLO generar CxP si es crédito (dias_plazo > 0) */
if ($dias_plazo > 0) {

    $stmtCxp = $conexion->prepare(
        "INSERT INTO cxp (compra_id, proveedor_id, condicion_id, fecha, fecha_vencimiento, monto, saldo, activo)
         VALUES (?, ?, ?, ?, ?, ?, ?, 1)"
    );

    $saldo = $total;

    $stmtCxp->bind_param("iiissdd",
        $compra_id,
        $proveedor_id,
        $condicion_id,
        $fecha,
        $fecha_venc,
        $total,
        $saldo
    );

    if (!$stmtCxp->execute()) 
        throw new Exception("Error insert CXP: " . $stmtCxp->error);

}
/* Si es contado: NO crear CxP y NO registrar pago automático */


        /* ---------------- SI ES CONTADO → PAGAR ---------------- */
        if ($dias_plazo === 0) {
            $stmtPago = $conexion->prepare(
                "INSERT INTO pagos_cxp (cxp_id, monto, fecha, usuario_id)
                 VALUES (?, ?, ?, ?)"
            );
            $cxp_id = $conexion->insert_id;

            $stmtPago->bind_param("idsi", $cxp_id, $total, $fecha, $usuario_id);
            $stmtPago->execute();

            $conexion->query("UPDATE cxp SET saldo = 0 WHERE id_cxp = $cxp_id");
        }

        $conexion->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Compra registrada correctamente',
            'compra_id' => $compra_id
        ]);

    } catch (Exception $e) {
        $conexion->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

/* =======================================================
                    LISTAR PROVEEDORES
======================================================= */

function listarProveedores($conexion){
    $q = "SELECT id_proveedores, nombre FROM proveedor WHERE activo = 1 ORDER BY nombre";
    $res = $conexion->query($q);
    $data = [];
    while ($r = $res->fetch_assoc()) $data[] = $r;

    echo json_encode(['success'=>true, 'data'=>$data]);
}

/* =======================================================
                    LISTAR PRODUCTOS
======================================================= */

function listarProductos($conexion){
    $q = "SELECT id_productos, nombre, stock, costo FROM producto WHERE activo = 1 ORDER BY nombre";
    $res = $conexion->query($q);

    $data = [];
    while ($r = $res->fetch_assoc()) {
        $r['stock'] = floatval($r['stock']);
        $r['costo'] = floatval($r['costo']);
        $data[] = $r;
    }
    echo json_encode(['success'=>true, 'data'=>$data]);
}

/* =======================================================
                    LISTAR CONDICIONES
======================================================= */

function listarCondiciones($conexion){
    $res = $conexion->query("SELECT id_condiciones_pago, nombre, dias_plazo FROM condicion_pago WHERE activo = 1");
    $data = [];
    while ($r = $res->fetch_assoc()) $data[] = $r;

    echo json_encode(['success'=>true, 'data'=>$data]);
}

/* =======================================================
                    GENERAR CORRELATIVO
======================================================= */

function generarNumero($conexion){
    $res = $conexion->query("SELECT numero_documento FROM compra ORDER BY id_compras DESC LIMIT 1");

    if ($res && $res->num_rows > 0){
        $row = $res->fetch_assoc();
        if (preg_match('/CMP-(\d+)/', $row['numero_documento'], $m))
            $num = intval($m[1]) + 1;
        else 
            $num = 1;
    } else $num = 1;

    $nuevo = 'CMP-' . str_pad($num, 8, '0', STR_PAD_LEFT);

    echo json_encode(['success'=>true, 'numero'=>$nuevo]);
}

/* =======================================================
                    LISTAR COMPRAS
======================================================= */

function listarCompras($conexion){
    $q = "SELECT c.*, p.nombre AS proveedor_nombre 
          FROM compra c
          LEFT JOIN proveedor p ON c.proveedor_id = p.id_proveedores
          ORDER BY c.id_compras DESC LIMIT 100";

    $res = $conexion->query($q);
    $data = [];

    while ($r = $res->fetch_assoc()) $data[] = $r;

    echo json_encode(['success'=>true, 'data'=>$data]);
}

/* =======================================================
                    OBTENER COMPRA (EDITAR/IMPRIMIR)
======================================================= */

function obtenerCompra($conexion, $id){
    $stmt = $conexion->prepare(
        "SELECT c.*, p.nombre AS proveedor_nombre
         FROM compra c
         LEFT JOIN proveedor p ON c.proveedor_id = p.id_proveedores
         WHERE c.id_compras = ?
         LIMIT 1"
    );

    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res && $res->num_rows > 0) {

        $comp = $res->fetch_assoc();

        $stmt2 = $conexion->prepare(
            "SELECT cd.*, pr.nombre AS producto_nombre
             FROM compra_detalle cd
             LEFT JOIN producto pr ON cd.producto_id = pr.id_productos
             WHERE cd.compra_id = ? AND cd.activo = 1"
        );

        $stmt2->bind_param("i", $id);
        $stmt2->execute();

        $r2 = $stmt2->get_result();
        $det = [];

        while ($row = $r2->fetch_assoc()) $det[] = $row;

        $comp['detalle'] = $det;

        echo json_encode(['success'=>true, 'data'=>$comp]);
    } else {
        echo json_encode(['success'=>false, 'message'=>'Compra no encontrada']);
    }
}
?>
