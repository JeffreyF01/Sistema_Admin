<?php
// Cotizacion_ajax.php (versión corregida)
// Lectura: acepta tanto JSON en body como POST form (jQuery $.post)

header('Content-Type: application/json; charset=utf-8');
session_start();
require_once '../conexion.php';

// lectura del body raw (soporta JSON)
$raw = file_get_contents('php://input');
$data = [];
if ($raw) {
    $decoded = json_decode($raw, true);
    if (is_array($decoded)) $data = $decoded;
}

// también soporta post form simple
$accion = $data['accion'] ?? $_POST['accion'] ?? '';

// helper para respuesta JSON
function json_resp($ok, $payload = []) {
    echo json_encode(array_merge(['success' => $ok], $payload));
    exit;
}

// helper para obtener parámetros (desde JSON body o POST)
function get_param($key, $default = null) {
    global $data, $_POST;
    if (isset($data[$key])) return $data[$key];
    if (isset($_POST[$key])) return $_POST[$key];
    return $default;
}

// --------------------- LISTAR cotizaciones (recientes) ---------------------
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

// --------------------- LISTAR CLIENTES ---------------------
if ($accion === 'listar_clientes') {
    $sql = "SELECT id_clientes, nombre FROM cliente WHERE activo = 1 ORDER BY nombre";
    $res = $conexion->query($sql);
    $out = [];
    while ($r = $res->fetch_assoc()) $out[] = $r;
    json_resp(true, ['data' => $out]);
}

// --------------------- LISTAR PRODUCTOS (para selección) ---------------------
if ($accion === 'listar_productos') {
    // ajusta campos si tu tabla tiene nombres distintos
    $sql = "SELECT id_productos, nombre, precio_venta FROM producto WHERE activo = 1 ORDER BY nombre";
    $res = $conexion->query($sql);
    $out = [];
    while ($r = $res->fetch_assoc()) $out[] = $r;
    json_resp(true, ['data' => $out]);
}

// --------------------- GENERAR NÚMERO ---------------------
if ($accion === 'generar_numero') {
    $sql = "SELECT MAX(id_cotizaciones) as maxid FROM cotizacion";
    $res = $conexion->query($sql);
    $row = $res->fetch_assoc();
    $next = ((int)$row['maxid']) + 1;
    $numero = 'COT-' . str_pad($next, 6, '0', STR_PAD_LEFT);
    json_resp(true, ['numero' => $numero]);
}

// --------------------- OBTENER una cotizacion y su detalle ---------------------
$id_param = get_param('id', null);
if ($accion === 'obtener' && $id_param !== null) {
    $id = (int)$id_param;

    $stmt = $conexion->prepare("SELECT * FROM cotizacion WHERE id_cotizaciones = ? LIMIT 1");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 0) json_resp(false, ['message' => 'Cotización no encontrada']);

    $cot = $res->fetch_assoc();

    // detalle
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

// --------------------- GUARDAR (guardar o editar) ---------------------
if (in_array($accion, ['guardar', 'editar'])) {
    // mezcla: JSON + POST + FormData
    $payload = array_merge($_POST, $data);

    // validar mínimos
    $numero = $payload['numero_documento'] ?? '';
    $fecha = $payload['fecha'] ?? '';
    $valida = $payload['valida_hasta'] ?? '';
    $cliente_id = (int)($payload['cliente_id'] ?? 0);

    // detalle puede llegar como JSON string desde FormData
    if (isset($payload['detalle']) && is_string($payload['detalle'])) {
        $decoded = json_decode($payload['detalle'], true);
        if (is_array($decoded)) $payload['detalle'] = $decoded;
    }

    $detalle = $payload['detalle'] ?? [];

    if (!$numero || !$fecha || !$valida || $cliente_id <= 0 || !is_array($detalle) || count($detalle) === 0) {
        json_resp(false, ['message' => 'Datos incompletos']);
    }

    // calcular total
    $total = 0;
    foreach ($detalle as $it) {
        $total += floatval($it['subtotal'] ?? ($it['cantidad'] * $it['precio_unitario']));
    }

    if ($accion === 'guardar') {
        // insertar cotizacion
        $stmt = $conexion->prepare("INSERT INTO cotizacion (numero_documento, cliente_id, usuario_id, fecha, valida_hasta, total, activo) VALUES (?, ?, ?, ?, ?, ?, 1)");
        $usuario_id = $_SESSION['user_info']['id_usuarios'] ?? null;
        $stmt->bind_param("siissd", $numero, $cliente_id, $usuario_id, $fecha, $valida, $total);
        if (!$stmt->execute()) json_resp(false, ['message' => 'Error al insertar cotización: ' . $conexion->error]);
        $cot_id = $conexion->insert_id;

        // insertar detalles
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
        // editar: validar ID
        $id = (int)($payload['id'] ?? 0);
        if ($id <= 0) json_resp(false, ['message' => 'ID inválido']);

        // verificar que la cotizacion no esté convertida (activo=0)
        $r = $conexion->query("SELECT activo FROM cotizacion WHERE id_cotizaciones = " . intval($id) . " LIMIT 1");
        if ($r && $row = $r->fetch_assoc()) {
            if ((int)$row['activo'] === 0) {
                json_resp(false, ['message' => 'No se puede editar una cotización ya convertida a factura (bloqueada).']);
            }
        }

        $stmt = $conexion->prepare("UPDATE cotizacion SET numero_documento = ?, cliente_id = ?, fecha = ?, valida_hasta = ?, total = ? WHERE id_cotizaciones = ?");
        $stmt->bind_param("sissdi", $numero, $cliente_id, $fecha, $valida, $total, $id);
        if (!$stmt->execute()) json_resp(false, ['message' => 'Error al actualizar cotización: ' . $conexion->error]);

        // eliminar detalle antiguo y reinsertar
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

// --------------------- ANULAR ---------------------
$id_param = get_param('id', null);
if ($accion === 'anular' && $id_param !== null) {
    $id = (int)$id_param;
    $stmt = $conexion->prepare("UPDATE cotizacion SET activo = 0 WHERE id_cotizaciones = ? LIMIT 1");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) json_resp(true, ['message' => 'Cotización anulada']);
    else json_resp(false, ['message' => 'Error al anular: ' . $conexion->error]);
}

// --------------------- CONVERTIR A FACTURA ---------------------
if ($accion === 'convertir_a_factura' && $id_param !== null) {
    $id = (int)$id_param;
    // obtener cotizacion
    $r = $conexion->query("SELECT * FROM cotizacion WHERE id_cotizaciones = " . intval($id) . " LIMIT 1");
    if ($r->num_rows === 0) json_resp(false, ['message' => 'Cotización no encontrada']);
    $cot = $r->fetch_assoc();

    // si ya está inactiva -> ya convertida / anulada
    if ((int)$cot['activo'] === 0) {
        json_resp(false, ['message' => 'La cotización ya fue convertida / está bloqueada.']);
    }

    $detRes = $conexion->query("SELECT * FROM cotizacion_detalle WHERE cotizacion_id = " . intval($id));
    $det = [];
    while ($d = $detRes->fetch_assoc()) $det[] = $d;

    // intentar insertar en facturas
    $conexion->begin_transaction();
    try {
        // obtener una condicion de pago válida (evitar NULL)
        $condRes = $conexion->query("SELECT id_condiciones_pago FROM condicion_pago WHERE activo = 1 LIMIT 1");
        if ($condRes && $condRes->num_rows > 0) {
            $condRow = $condRes->fetch_assoc();
            $condicion_id = (int)$condRow['id_condiciones_pago'];
        } else {
            // fallback: usa 1 (ajusta si tu sistema no tiene id=1)
            $condicion_id = 1;
        }

        $stmt = $conexion->prepare("INSERT INTO factura (numero_documento, cliente_id, usuario_id, fecha, condicion_id, total, activo) VALUES (?, ?, ?, ?, ?, ?, 1)");
        $numero_fact = 'FAC-' . date('YmdHis');
        $usuario_id = $_SESSION['user_info']['id_usuarios'] ?? null;
        $fecha = date('Y-m-d');
        $total = floatval($cot['total']);
        $stmt->bind_param("siisid", $numero_fact, $cot['cliente_id'], $usuario_id, $fecha, $condicion_id, $total);
        if (!$stmt->execute()) throw new Exception('Error insert facturas: ' . $conexion->error);
        $factura_id = $conexion->insert_id;

        $stmtDet = $conexion->prepare("INSERT INTO factura_detalle (factura_id, producto_id, cantidad, precio_unitario, activo) VALUES (?, ?, ?, ?, 1)");
        foreach ($det as $it) {
            $p = (int)$it['producto_id'];
            $cant = floatval($it['cantidad']);
            $prec = floatval($it['precio_unitario']);
            $stmtDet->bind_param("iidd", $factura_id, $p, $cant, $prec);
            if (!$stmtDet->execute()) throw new Exception('Error insert factura_detalle: ' . $conexion->error);
        }

        // MARCAR COTIZACIÓN COMO CONVERTIDA (inactiva)
        $q = $conexion->prepare("UPDATE cotizacion SET activo = 0 WHERE id_cotizaciones = ? LIMIT 1");
        $q->bind_param("i", $id);
        if (!$q->execute()) throw new Exception('Error marcando cotización como convertida: ' . $conexion->error);

        $conexion->commit();
        json_resp(true, ['message' => 'Cotización convertida a factura', 'factura_id' => $factura_id]);
    } catch (Exception $e) {
        $conexion->rollback();
        json_resp(false, ['message' => 'No se pudo convertir: ' . $e->getMessage()]);
    }
}

// Si llega aquí, acción no soportada
json_resp(false, ['message' => 'Acción inválida']);
