<?php
header('Content-Type: application/json; charset=utf-8');
session_start();
require_once '../conexion.php';

$raw = file_get_contents('php://input');
$data = [];
if ($raw) {
    $decoded = json_decode($raw, true);
    if (is_array($decoded)) $data = $decoded;
}

$accion = $data['accion'] ?? $_POST['accion'] ?? '';

function json_resp($ok, $payload = []) {
    echo json_encode(array_merge(['success' => $ok], $payload));
    exit;
}

function get_param($key, $default = null) {
    global $data, $_POST;
    if (isset($data[$key])) return $data[$key];
    if (isset($_POST[$key])) return $_POST[$key];
    return $default;
}

if ($accion === 'listar') {
    $sql = "SELECT c.id_cotizaciones, c.numero_documento, c.cliente_id, cli.nombre AS cliente_nombre,
                   DATE_FORMAT(c.fecha, '%Y-%m-%d') as fecha,
                   DATE_FORMAT(c.valida_hasta, '%Y-%m-%d') as valida_hasta,
                   c.total, c.activo
            FROM cotizacion c
            LEFT JOIN cliente cli ON cli.id_clientes = c.cliente_id
            ORDER BY c.id_cotizaciones DESC
            LIMIT 100";
    $res = $conexion->query($sql);
    $out = [];
    while ($r = $res->fetch_assoc()) {
        $out[] = $r;
    }
    json_resp(true, ['data' => $out]);
}

if ($accion === 'listar_clientes') {
    $sql = "SELECT id_clientes, nombre FROM cliente WHERE activo = 1 ORDER BY nombre";
    $res = $conexion->query($sql);
    $out = [];
    while ($r = $res->fetch_assoc()) $out[] = $r;
    json_resp(true, ['data' => $out]);
}

if ($accion === 'listar_productos') {
    $sql = "SELECT id_productos, nombre, stock, precio_venta FROM producto WHERE activo = 1 ORDER BY nombre";
    $res = $conexion->query($sql);
    $out = [];
    while ($r = $res->fetch_assoc()) $out[] = $r;
    json_resp(true, ['data' => $out]);
}

if ($accion === 'generar_numero') {
    $sql = "SELECT MAX(id_cotizaciones) as maxid FROM cotizacion";
    $res = $conexion->query($sql);
    $row = $res->fetch_assoc();
    $next = ((int)$row['maxid']) + 1;
    $numero = 'COT-' . str_pad($next, 6, '0', STR_PAD_LEFT);
    json_resp(true, ['numero' => $numero]);
}

$id_param = get_param('id', null);
if ($accion === 'obtener' && $id_param !== null) {
    $id = (int)$id_param;

    $stmt = $conexion->prepare("SELECT * FROM cotizacion WHERE id_cotizaciones = ? LIMIT 1");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 0) json_resp(false, ['message' => 'Cotización no encontrada']);

    $cot = $res->fetch_assoc();

    $det = [];
    $stmt2 = $conexion->prepare("SELECT cd.*, p.nombre 
                                 FROM cotizacion_detalle cd 
                                 LEFT JOIN producto p ON p.id_productos = cd.producto_id 
                                 WHERE cd.cotizacion_id = ?");
    $stmt2->bind_param("i", $id);
    $stmt2->execute();
    $r2 = $stmt2->get_result();
    while ($d = $r2->fetch_assoc()) $det[] = $d;

    json_resp(true, [
        'data' => $cot,
        'detalle' => $det
    ]);
}

if (in_array($accion, ['guardar', 'editar'])) {
    $payload = array_merge($_POST, $data);

    $numero = $payload['numero_documento'] ?? '';
    $fecha = $payload['fecha'] ?? '';
    $valida = $payload['valida_hasta'] ?? '';
    $cliente_id = (int)($payload['cliente_id'] ?? 0);

    if (isset($payload['detalle']) && is_string($payload['detalle'])) {
        $decoded = json_decode($payload['detalle'], true);
        if (is_array($decoded)) $payload['detalle'] = $decoded;
    }

    $detalle = $payload['detalle'] ?? [];

    if (!$numero || !$fecha || !$valida || $cliente_id <= 0 || !is_array($detalle) || count($detalle) === 0) {
        json_resp(false, ['message' => 'Datos incompletos']);
    }

    $total = 0;
    foreach ($detalle as $it) {
        $total += floatval($it['subtotal'] ?? ($it['cantidad'] * $it['precio_unitario']));
    }

    if ($accion === 'guardar') {
        $stmt = $conexion->prepare("INSERT INTO cotizacion (numero_documento, cliente_id, usuario_id, fecha, valida_hasta, total, activo) VALUES (?, ?, ?, ?, ?, ?, 1)");
        $usuario_id = $_SESSION['user_info']['id_usuarios'] ?? null;
        $stmt->bind_param("siissd", $numero, $cliente_id, $usuario_id, $fecha, $valida, $total);
        if (!$stmt->execute()) json_resp(false, ['message' => 'Error al insertar cotización: ' . $conexion->error]);
        $cot_id = $conexion->insert_id;

        $stmtDet = $conexion->prepare("INSERT INTO cotizacion_detalle (cotizacion_id, producto_id, cantidad, precio_unitario, activo) VALUES (?, ?, ?, ?, 1)");
        foreach ($detalle as $it) {
            $pid = (int)$it['producto_id'];
            $cantidad = floatval($it['cantidad']);
            $precio = floatval($it['precio_unitario']);
            $stmtDet->bind_param("iidd", $cot_id, $pid, $cantidad, $precio);
            $stmtDet->execute();
        }

        json_resp(true, ['message' => 'Cotización guardada', 'id' => $cot_id]);
    } else {
        $id = (int)($payload['id'] ?? 0);
        if ($id <= 0) json_resp(false, ['message' => 'ID inválido']);

        $r = $conexion->query("SELECT activo FROM cotizacion WHERE id_cotizaciones = " . intval($id) . " LIMIT 1");
        if ($r && $row = $r->fetch_assoc()) {
            if ((int)$row['activo'] === 0) {
                json_resp(false, ['message' => 'No se puede editar una cotización ya convertida a factura (bloqueada).']);
            }
        }

        $stmt = $conexion->prepare("UPDATE cotizacion SET numero_documento = ?, cliente_id = ?, fecha = ?, valida_hasta = ?, total = ? WHERE id_cotizaciones = ?");
        $stmt->bind_param("sissdi", $numero, $cliente_id, $fecha, $valida, $total, $id);
        if (!$stmt->execute()) json_resp(false, ['message' => 'Error al actualizar cotización: ' . $conexion->error]);

        $conexion->query("DELETE FROM cotizacion_detalle WHERE cotizacion_id = " . intval($id));
        $stmtDet = $conexion->prepare("INSERT INTO cotizacion_detalle (cotizacion_id, producto_id, cantidad, precio_unitario, activo) VALUES (?, ?, ?, ?, 1)");
        foreach ($detalle as $it) {
            $pid = (int)$it['producto_id'];
            $cantidad = floatval($it['cantidad']);
            $precio = floatval($it['precio_unitario']);
            $stmtDet->bind_param("iidd", $id, $pid, $cantidad, $precio);
            $stmtDet->execute();
        }
        json_resp(true, ['message' => 'Cotización actualizada', 'id' => $id]);
    }
}

$id_param = get_param('id', null);
if ($accion === 'anular' && $id_param !== null) {
    $id = (int)$id_param;
    $stmt = $conexion->prepare("UPDATE cotizacion SET activo = 0 WHERE id_cotizaciones = ? LIMIT 1");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) json_resp(true, ['message' => 'Cotización anulada']);
    else json_resp(false, ['message' => 'Error al anular: ' . $conexion->error]);
}

if ($accion === 'convertir_a_factura' && $id_param !== null) {
    $id = (int)$id_param;

    // obtener cabecera cotizacion
    $r = $conexion->query("SELECT * FROM cotizacion WHERE id_cotizaciones = " . intval($id) . " LIMIT 1");
    if ($r->num_rows === 0) json_resp(false, ['message' => 'Cotización no encontrada']);
    $cot = $r->fetch_assoc();

    // si ya está bloqueada / convertida
    if ((int)$cot['activo'] === 0) {
        json_resp(false, ['message' => 'La cotización ya fue convertida / está bloqueada.']);
    }

    // obtener detalle
    $detRes = $conexion->query("SELECT * FROM cotizacion_detalle WHERE cotizacion_id = " . intval($id));
    $det = [];
    while ($d = $detRes->fetch_assoc()) $det[] = $d;

    $conexion->begin_transaction();
    try {

        // condición de pago
        $condRes = $conexion->query("SELECT id_condiciones_pago FROM condicion_pago WHERE activo = 1 LIMIT 1");
        if ($condRes && $condRes->num_rows > 0) {
            $condRow = $condRes->fetch_assoc();
            $condicion_id = (int)$condRow['id_condiciones_pago'];
        } else {
            $condicion_id = 1;
        }

        // insertar factura
        $stmt = $conexion->prepare("
            INSERT INTO factura (numero_documento, cliente_id, usuario_id, fecha, condicion_id, total, activo) 
            VALUES (?, ?, ?, ?, ?, ?, 1)
        ");

        $numero_fact = 'FAC-' . date('YmdHis');
        $usuario_id = $_SESSION['user_info']['id_usuarios'] ?? null;
        $fecha = date('Y-m-d');
        $total = floatval($cot['total']);

        $stmt->bind_param("siisid", $numero_fact, $cot['cliente_id'], $usuario_id, $fecha, $condicion_id, $total);
        if (!$stmt->execute()) throw new Exception('Error insert factura: ' . $conexion->error);
        $factura_id = $conexion->insert_id;

        // insertar detalle y RESTAR INVENTARIO
        $stmtDet = $conexion->prepare("
            INSERT INTO factura_detalle (factura_id, producto_id, cantidad, precio_unitario, activo) 
            VALUES (?, ?, ?, ?, 1)
        ");

        foreach ($det as $it) {

            $p = (int)$it['producto_id'];
            $cant = floatval($it['cantidad']);
            $prec = floatval($it['precio_unitario']);

            // insertar detalle
            $stmtDet->bind_param("iidd", $factura_id, $p, $cant, $prec);
            if (!$stmtDet->execute()) throw new Exception('Error insert factura_detalle: ' . $conexion->error);

            $up = $conexion->prepare("
                UPDATE producto 
                SET stock = stock - ? 
                WHERE id_productos = ? LIMIT 1
            ");
            $up->bind_param("di", $cant, $p);
            if (!$up->execute()) throw new Exception('Error restando inventario: ' . $conexion->error);
        }

        $q = $conexion->prepare("UPDATE cotizacion SET activo = 0 WHERE id_cotizaciones = ? LIMIT 1");
        $q->bind_param("i", $id);
        if (!$q->execute()) throw new Exception('Error marcando cotización como convertida: ' . $conexion->error);

        $conexion->commit();
        json_resp(true, [
            'message' => 'Cotización convertida a factura y stock actualizado',
            'factura_id' => $factura_id
        ]);

    } catch (Exception $e) {
        $conexion->rollback();
        json_resp(false, ['message' => 'No se pudo convertir: ' . $e->getMessage()]);
    }
}


json_resp(false, ['message' => 'Acción inválida']);