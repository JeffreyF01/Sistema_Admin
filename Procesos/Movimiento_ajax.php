<?php
session_start();
require_once '../conexion.php';
header('Content-Type: application/json');

if (!isset($_SESSION['usuario'])) {
    echo json_encode(['success' => false, 'message' => 'Sesión inválida']);
    exit;
}

// Obtener ID de usuario activo
$stmtUsr = $conexion->prepare('SELECT id_usuarios FROM usuario WHERE usuario = ? LIMIT 1');
$stmtUsr->bind_param('s', $_SESSION['usuario']);
$stmtUsr->execute();
$userRes = $stmtUsr->get_result();
$usuario_id = $userRes->fetch_assoc()['id_usuarios'] ?? null;

// Leer payload (JSON o form-data)
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!is_array($data) || empty($data)) {
    $data = $_POST;
}

$accion = $data['accion'] ?? '';

try {
    switch ($accion) {
        case 'listar_tipos':
            listarTipos($conexion);
            break;
        case 'listar_productos':
            listarProductos($conexion);
            break;
        case 'generar_numero':
            generarNumero($conexion);
            break;
        case 'guardar':
            guardarMovimiento($conexion, $data, $usuario_id);
            break;
        case 'listar':
            listarMovimientos($conexion);
            break;
        case 'obtener':
            obtenerMovimiento($conexion, $data['numero_documento'] ?? '');
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

/* =======================================================
                        GUARDAR
======================================================= */
function guardarMovimiento(mysqli $conexion, array $data, ?int $usuario_id): void
{
    if (!$usuario_id) {
        throw new Exception('Usuario no identificado');
    }

    $numero = trim($data['numero_documento'] ?? '');
    $tipo_id = intval($data['tipo_movimiento_id'] ?? 0);
    $fecha = !empty($data['fecha']) ? $data['fecha'] : date('Y-m-d');
    $referencia = trim($data['referencia'] ?? '');
    $detalle = $data['detalle'] ?? [];

    if ($numero === '' || $tipo_id <= 0 || !is_array($detalle) || count($detalle) === 0) {
        echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
        return;
    }

    $stmtTipo = $conexion->prepare('SELECT clase, nombre FROM tipo_movimiento WHERE id_tipos_movimiento = ? AND activo = 1');
    $stmtTipo->bind_param('i', $tipo_id);
    $stmtTipo->execute();
    $tipoRow = $stmtTipo->get_result()->fetch_assoc();

    if (!$tipoRow) {
        throw new Exception('Tipo de movimiento inválido o inactivo');
    }

    $clase = $tipoRow['clase']; // ENTRADA | SALIDA

    $stmtCheck = $conexion->prepare('SELECT 1 FROM movimiento_inventario WHERE numero_documento = ? LIMIT 1');
    $stmtCheck->bind_param('s', $numero);
    $stmtCheck->execute();
    $exists = $stmtCheck->get_result()->num_rows > 0;
    if ($exists) {
        echo json_encode(['success' => false, 'message' => 'El número de documento ya existe']);
        return;
    }

    // Se usará una sola fila en movimiento_inventario (cabecera) y múltiples en movimiento_inventario_detalle
    $conexion->begin_transaction();

    try {
        $stmtProd = $conexion->prepare('SELECT id_productos, stock, costo FROM producto WHERE id_productos = ? AND activo = 1 FOR UPDATE');
        $stmtAdd = $conexion->prepare('UPDATE producto SET stock = stock + ? WHERE id_productos = ?');
        $stmtSub = $conexion->prepare('UPDATE producto SET stock = stock - ? WHERE id_productos = ?');
        $stmtCab = $conexion->prepare('INSERT INTO movimiento_inventario (numero_documento, producto_id, tipo_movimiento_id, usuario_id, fecha, cantidad, referencia, activo) VALUES (?, ?, ?, ?, ?, ?, ?, 1)');
        $stmtDet = $conexion->prepare('INSERT INTO movimiento_inventario_detalle (movimiento_inventario_id, producto_id, cantidad, costo_unitario, activo) VALUES (?, ?, ?, ?, 1)');

        $totalCantidad = 0;
        foreach ($detalle as $linea) {
            $totalCantidad += floatval($linea['cantidad'] ?? 0);
        }

        $productoCab = intval($detalle[0]['producto_id'] ?? 0);
        $stmtCab->bind_param('siiisds', $numero, $productoCab, $tipo_id, $usuario_id, $fecha, $totalCantidad, $referencia);
        if (!$stmtCab->execute()) {
            throw new Exception('Error al registrar cabecera: ' . $stmtCab->error);
        }

        $movimientoId = $conexion->insert_id;

        foreach ($detalle as $linea) {
            $producto_id = intval($linea['producto_id'] ?? 0);
            $cantidad = floatval($linea['cantidad'] ?? 0);
            $costo_unitario = isset($linea['costo_unitario']) ? floatval($linea['costo_unitario']) : null;

            if ($producto_id <= 0 || $cantidad <= 0) {
                throw new Exception('Línea de detalle inválida');
            }

            $stmtProd->bind_param('i', $producto_id);
            $stmtProd->execute();
            $prodRes = $stmtProd->get_result();
            $prod = $prodRes->fetch_assoc();

            if (!$prod) {
                throw new Exception('Producto no encontrado o inactivo: ID ' . $producto_id);
            }

            $stockActual = floatval($prod['stock']);
            $costoActual = floatval($prod['costo']);

            if ($clase === 'SALIDA' && $stockActual < $cantidad) {
                throw new Exception('Stock insuficiente para el producto ID ' . $producto_id . '. Disponible: ' . $stockActual);
            }

            if ($clase === 'ENTRADA') {
                $stmtAdd->bind_param('di', $cantidad, $producto_id);
                if (!$stmtAdd->execute()) {
                    throw new Exception('Error al actualizar stock (entrada): ' . $stmtAdd->error);
                }
            } else {
                $stmtSub->bind_param('di', $cantidad, $producto_id);
                if (!$stmtSub->execute()) {
                    throw new Exception('Error al actualizar stock (salida): ' . $stmtSub->error);
                }
            }

            $costoGuardar = $costo_unitario !== null ? $costo_unitario : $costoActual;
            $stmtDet->bind_param('iidd', $movimientoId, $producto_id, $cantidad, $costoGuardar);
            if (!$stmtDet->execute()) {
                throw new Exception('Error al registrar detalle: ' . $stmtDet->error);
            }
        }

        $conexion->commit();
        echo json_encode(['success' => true, 'message' => 'Movimiento registrado', 'numero_documento' => $numero]);
    } catch (Exception $e) {
        $conexion->rollback();
        throw $e;
    }
}

/* =======================================================
                        LISTAR
======================================================= */
function listarMovimientos(mysqli $conexion): void
{
    $sql = "SELECT 
                mi.id_movimientos_inventario AS id,
                mi.numero_documento,
                tm.nombre AS tipo_nombre,
                tm.clase,
                mi.fecha,
                COALESCE(mi.referencia, '') AS referencia,
                COUNT(mid.id_movi_invd) AS lineas,
                COALESCE(SUM(mid.cantidad), 0) AS total_cantidad
            FROM movimiento_inventario mi
            INNER JOIN tipo_movimiento tm ON tm.id_tipos_movimiento = mi.tipo_movimiento_id
            LEFT JOIN movimiento_inventario_detalle mid ON mid.movimiento_inventario_id = mi.id_movimientos_inventario AND mid.activo = 1
            WHERE mi.activo = 1
            GROUP BY mi.id_movimientos_inventario, mi.numero_documento, tm.nombre, tm.clase, mi.fecha, mi.referencia
            ORDER BY mi.id_movimientos_inventario DESC
            LIMIT 150";

    $res = $conexion->query($sql);
    $data = [];
    while ($row = $res->fetch_assoc()) {
        $row['total_cantidad'] = floatval($row['total_cantidad']);
        $data[] = $row;
    }

    echo json_encode(['success' => true, 'data' => $data]);
}

/* =======================================================
                        OBTENER
======================================================= */
function obtenerMovimiento(mysqli $conexion, string $numero): void
{
    if ($numero === '') {
        echo json_encode(['success' => false, 'message' => 'Número requerido']);
        return;
    }

    $stmtCab = $conexion->prepare('SELECT 
            mi.id_movimientos_inventario,
            mi.numero_documento,
            tm.nombre AS tipo_nombre,
            tm.clase,
            mi.tipo_movimiento_id,
            mi.fecha,
            COALESCE(mi.referencia, "") AS referencia
        FROM movimiento_inventario mi
        INNER JOIN tipo_movimiento tm ON tm.id_tipos_movimiento = mi.tipo_movimiento_id
        WHERE mi.numero_documento = ? AND mi.activo = 1
        LIMIT 1');
    $stmtCab->bind_param('s', $numero);
    $stmtCab->execute();
    $cabRes = $stmtCab->get_result();

    if ($cabRes->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Movimiento no encontrado']);
        return;
    }

    $cab = $cabRes->fetch_assoc();

    $stmtDet = $conexion->prepare('SELECT 
            mid.producto_id,
            p.nombre AS producto_nombre,
            p.sku,
            mid.cantidad,
            mid.costo_unitario
        FROM movimiento_inventario_detalle mid
        INNER JOIN producto p ON p.id_productos = mid.producto_id
        WHERE mid.movimiento_inventario_id = ? AND mid.activo = 1
        ORDER BY mid.id_movi_invd');
    $stmtDet->bind_param('i', $cab['id_movimientos_inventario']);
    $stmtDet->execute();
    $detRes = $stmtDet->get_result();

    $det = [];
    while ($r = $detRes->fetch_assoc()) {
        $r['cantidad'] = floatval($r['cantidad']);
        $r['costo_unitario'] = $r['costo_unitario'] !== null ? floatval($r['costo_unitario']) : null;
        $det[] = $r;
    }

    echo json_encode(['success' => true, 'data' => array_merge($cab, ['detalle' => $det])]);
}

/* =======================================================
                    LISTAR TIPOS
======================================================= */
function listarTipos(mysqli $conexion): void
{
    $res = $conexion->query('SELECT id_tipos_movimiento, nombre, clase FROM tipo_movimiento WHERE activo = 1 ORDER BY nombre');
    $data = [];
    while ($r = $res->fetch_assoc()) {
        $data[] = $r;
    }

    echo json_encode(['success' => true, 'data' => $data]);
}

/* =======================================================
                    LISTAR PRODUCTOS
======================================================= */
function listarProductos(mysqli $conexion): void
{
    $res = $conexion->query('SELECT id_productos, sku, nombre, stock, costo FROM producto WHERE activo = 1 ORDER BY nombre');
    $data = [];
    while ($r = $res->fetch_assoc()) {
        $r['stock'] = floatval($r['stock']);
        $r['costo'] = floatval($r['costo']);
        $data[] = $r;
    }

    echo json_encode(['success' => true, 'data' => $data]);
}

/* =======================================================
                    GENERAR NUMERO
======================================================= */
function generarNumero(mysqli $conexion): void
{
    $res = $conexion->query('SELECT numero_documento FROM movimiento_inventario ORDER BY id_movimientos_inventario DESC LIMIT 1');
    $num = 1;

    if ($res && $res->num_rows > 0) {
        $row = $res->fetch_assoc();
        if (preg_match('/MOV-(\d+)/', $row['numero_documento'], $m)) {
            $num = intval($m[1]) + 1;
        }
    }

    $nuevo = 'MOV-' . str_pad((string)$num, 8, '0', STR_PAD_LEFT);
    echo json_encode(['success' => true, 'numero' => $nuevo]);
}
?>
