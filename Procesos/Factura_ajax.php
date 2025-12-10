<?php
session_start();
require_once '../conexion.php';

header('Content-Type: application/json');

// Verificar sesión
if(!isset($_SESSION['usuario'])) {
    echo json_encode(['success' => false, 'message' => 'Sesión no válida']);
    exit();
}

// Obtener usuario ID
$query_usuario = "SELECT id_usuarios FROM usuario WHERE usuario = ?";
$stmt_usuario = $conexion->prepare($query_usuario);
$stmt_usuario->bind_param("s", $_SESSION['usuario']);
$stmt_usuario->execute();
$result_usuario = $stmt_usuario->get_result();
$usuario_data = $result_usuario->fetch_assoc();
$usuario_id = $usuario_data['id_usuarios'];

// Procesar solicitud POST JSON
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Si no es JSON, usar $_POST
if (!$data) {
    $data = $_POST;
}

$accion = isset($data['accion']) ? $data['accion'] : '';

try {
    switch($accion) {
        case 'guardar':
            guardarFactura($conexion, $data, $usuario_id);
            break;
        case 'listar':
            listarFacturas($conexion);
            break;
        case 'obtener':
            obtenerFactura($conexion, $data['id']);
            break;
        case 'anular':
            anularFactura($conexion, $data['id']);
            break;
        case 'listar_clientes':
            listarClientes($conexion);
            break;
        case 'listar_condiciones':
            listarCondicionesPago($conexion);
            break;
        case 'listar_productos':
            listarProductos($conexion);
            break;
        case 'generar_numero':
            generarNumeroDocumento($conexion);
            break;
        case 'cargar_desde_cotizacion':
            cargarDesdeCotizacion($conexion, $data['id'] ?? null);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

function guardarFactura($conexion, $data, $usuario_id) {
    try {
        $conexion->begin_transaction();

        $numero_documento = $data['numero_documento'];
        $cliente_id = $data['cliente_id'];
        $fecha = $data['fecha'];
        $condicion_id = $data['condicion_id'];
        $detalle = $data['detalle'];
        $cotizacion_id = isset($data['cotizacion_id']) ? intval($data['cotizacion_id']) : 0;

        // Calcular total
        $total = 0;
        foreach($detalle as $item) {
            $total += $item['subtotal'];
        }

        // Obtener días de plazo para determinar si es contado o crédito
        $query_dias = "SELECT dias_plazo FROM condicion_pago WHERE id_condiciones_pago = ?";
        $stmt_dias = $conexion->prepare($query_dias);
        $stmt_dias->bind_param("i", $condicion_id);
        $stmt_dias->execute();
        $result_dias = $stmt_dias->get_result();
        $row_dias = $result_dias->fetch_assoc();
        $dias_plazo = intval($row_dias['dias_plazo'] ?? 1);

        // Determinar estado: Si dias_plazo = 0 es Contado (finalizado), si > 0 es Crédito (pendiente)
        $estado = ($dias_plazo == 0) ? 'finalizado' : 'pendiente';

        // Insertar factura
        $query = "INSERT INTO factura (numero_documento, cliente_id, usuario_id, fecha, condicion_id, total, estado, activo) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, 1)";
        $stmt = $conexion->prepare($query);
        $stmt->bind_param("siisids", $numero_documento, $cliente_id, $usuario_id, $fecha, $condicion_id, $total, $estado);
        
        if (!$stmt->execute()) {
            throw new Exception("Error al guardar la factura: " . $stmt->error);
        }

        $factura_id = $conexion->insert_id;

        // Insertar detalle y actualizar inventario
        $query_detalle = "INSERT INTO factura_detalle (factura_id, producto_id, cantidad, precio_unitario, activo) 
                          VALUES (?, ?, ?, ?, 1)";
        $stmt_detalle = $conexion->prepare($query_detalle);

        $query_update_stock = "UPDATE producto SET stock = stock - ? WHERE id_productos = ?";
        $stmt_stock = $conexion->prepare($query_update_stock);

        foreach($detalle as $item) {
            // Verificar stock disponible
            $query_check = "SELECT stock FROM producto WHERE id_productos = ?";
            $stmt_check = $conexion->prepare($query_check);
            $stmt_check->bind_param("i", $item['producto_id']);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();
            $producto = $result_check->fetch_assoc();
            
            $stock_disponible = floatval($producto['stock']);
            $cantidad_requerida = floatval($item['cantidad']);

            if ($stock_disponible < $cantidad_requerida) {
                throw new Exception("Stock insuficiente para el producto ID: " . $item['producto_id'] . ". Disponible: " . $stock_disponible);
            }

            // Insertar detalle
            $stmt_detalle->bind_param("iidd", $factura_id, $item['producto_id'], $item['cantidad'], $item['precio_unitario']);
            if (!$stmt_detalle->execute()) {
                throw new Exception("Error al guardar el detalle: " . $stmt_detalle->error);
            }

            // Actualizar stock
            $stmt_stock->bind_param("di", $item['cantidad'], $item['producto_id']);
            if (!$stmt_stock->execute()) {
                throw new Exception("Error al actualizar el stock: " . $stmt_stock->error);
            }

            // Registrar movimiento de inventario (salida)
            registrarMovimientoInventario($conexion, $item['producto_id'], $item['cantidad'], 
                                         $numero_documento, $fecha, $usuario_id, 'SALIDA');
        }

        // Si proviene de cotización editable, marcarla como convertida (inactiva)
        if ($cotizacion_id > 0) {
            $stmt_cot = $conexion->prepare("UPDATE cotizacion SET activo = 0 WHERE id_cotizaciones = ? AND activo = 1");
            $stmt_cot->bind_param("i", $cotizacion_id);
            $stmt_cot->execute();
        }

        $conexion->commit();
        echo json_encode([
            'success' => true, 
            'message' => 'Factura guardada correctamente',
            'factura_id' => $factura_id,
            'cotizacion_convertida' => $cotizacion_id > 0 ? $cotizacion_id : null
        ]);

    } catch (Exception $e) {
        $conexion->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function registrarMovimientoInventario($conexion, $producto_id, $cantidad, $referencia, $fecha, $usuario_id, $tipo_clase) {
    // Obtener tipo de movimiento según clase
    $query_tipo = "SELECT id_tipos_movimiento FROM tipo_movimiento WHERE clase = ? AND activo = 1 LIMIT 1";
    $stmt_tipo = $conexion->prepare($query_tipo);
    $stmt_tipo->bind_param("s", $tipo_clase);
    $stmt_tipo->execute();
    $result_tipo = $stmt_tipo->get_result();
    
    if ($result_tipo->num_rows > 0) {
        $tipo_mov = $result_tipo->fetch_assoc();
        $tipo_movimiento_id = $tipo_mov['id_tipos_movimiento'];

        // Generar número de documento para el movimiento
        $numero_mov = 'MOV-' . date('YmdHis') . '-' . rand(1000, 9999);

        $query_mov = "INSERT INTO movimiento_inventario (numero_documento, producto_id, tipo_movimiento_id, usuario_id, fecha, cantidad, referencia, activo) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, 1)";
        $stmt_mov = $conexion->prepare($query_mov);
        $stmt_mov->bind_param("siiisds", $numero_mov, $producto_id, $tipo_movimiento_id, $usuario_id, $fecha, $cantidad, $referencia);
        $stmt_mov->execute();
    }
}

function listarFacturas($conexion) {
    $query = "SELECT f.*, c.nombre as cliente_nombre, cp.nombre as condicion_nombre, u.nombre as usuario_nombre
              FROM factura f
              INNER JOIN cliente c ON f.cliente_id = c.id_clientes
              INNER JOIN condicion_pago cp ON f.condicion_id = cp.id_condiciones_pago
              INNER JOIN usuario u ON f.usuario_id = u.id_usuarios
              ORDER BY f.fecha DESC, f.id_facturas DESC
              LIMIT 50";
    
    $result = $conexion->query($query);
    $facturas = [];

    while($row = $result->fetch_assoc()) {
        $facturas[] = $row;
    }

    echo json_encode(['success' => true, 'data' => $facturas]);
}

function obtenerFactura($conexion, $id) {
    // Obtener factura
    $query = "SELECT f.*, c.nombre as cliente_nombre, c.doc_identidad, c.direccion, c.telefono,
                     cp.nombre as condicion_nombre, cp.dias_plazo, u.nombre as usuario_nombre
              FROM factura f
              INNER JOIN cliente c ON f.cliente_id = c.id_clientes
              INNER JOIN condicion_pago cp ON f.condicion_id = cp.id_condiciones_pago
              INNER JOIN usuario u ON f.usuario_id = u.id_usuarios
              WHERE f.id_facturas = ?";
    
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $factura = $result->fetch_assoc();

    // Obtener detalle con cantidades devueltas
    $query_detalle = "SELECT fd.*, p.nombre as producto_nombre, p.sku,
                      COALESCE((SELECT SUM(dd.cantidad) 
                                FROM devolucion_detalle dd
                                INNER JOIN devolucion d ON dd.devolucion_id = d.id_devoluciones
                                WHERE d.factura_id = fd.factura_id 
                                AND dd.producto_id = fd.producto_id 
                                AND d.activo = 1 
                                AND dd.activo = 1), 0) as cantidad_devuelta,
                      (fd.cantidad - COALESCE((SELECT SUM(dd.cantidad) 
                                               FROM devolucion_detalle dd
                                               INNER JOIN devolucion d ON dd.devolucion_id = d.id_devoluciones
                                               WHERE d.factura_id = fd.factura_id 
                                               AND dd.producto_id = fd.producto_id 
                                               AND d.activo = 1 
                                               AND dd.activo = 1), 0)) as cantidad_neta
                      FROM factura_detalle fd
                      INNER JOIN producto p ON fd.producto_id = p.id_productos
                      WHERE fd.factura_id = ? AND fd.activo = 1";
    
    $stmt_detalle = $conexion->prepare($query_detalle);
    $stmt_detalle->bind_param("i", $id);
    $stmt_detalle->execute();
    $result_detalle = $stmt_detalle->get_result();
    
    $detalle = [];
    while($row = $result_detalle->fetch_assoc()) {
        $detalle[] = $row;
    }

    $factura['detalle'] = $detalle;

    echo json_encode(['success' => true, 'data' => $factura]);
}

function anularFactura($conexion, $id) {
    try {
        $conexion->begin_transaction();

        // Obtener detalle de la factura
        $query_detalle = "SELECT producto_id, cantidad FROM factura_detalle WHERE factura_id = ? AND activo = 1";
        $stmt_detalle = $conexion->prepare($query_detalle);
        $stmt_detalle->bind_param("i", $id);
        $stmt_detalle->execute();
        $result_detalle = $stmt_detalle->get_result();

        // Devolver stock
        $query_update_stock = "UPDATE producto SET stock = stock + ? WHERE id_productos = ?";
        $stmt_stock = $conexion->prepare($query_update_stock);

        while($item = $result_detalle->fetch_assoc()) {
            $stmt_stock->bind_param("di", $item['cantidad'], $item['producto_id']);
            $stmt_stock->execute();
        }

        // Anular factura
        $query_anular = "UPDATE factura SET activo = 0 WHERE id_facturas = ?";
        $stmt_anular = $conexion->prepare($query_anular);
        $stmt_anular->bind_param("i", $id);
        $stmt_anular->execute();

        // Anular detalle
        $query_anular_detalle = "UPDATE factura_detalle SET activo = 0 WHERE factura_id = ?";
        $stmt_anular_detalle = $conexion->prepare($query_anular_detalle);
        $stmt_anular_detalle->bind_param("i", $id);
        $stmt_anular_detalle->execute();

        $conexion->commit();
        echo json_encode(['success' => true, 'message' => 'Factura anulada correctamente']);

    } catch (Exception $e) {
        $conexion->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function listarClientes($conexion) {
    $query = "SELECT id_clientes, nombre, doc_identidad FROM cliente WHERE activo = 1 ORDER BY nombre";
    $result = $conexion->query($query);
    $clientes = [];

    while($row = $result->fetch_assoc()) {
        $clientes[] = $row;
    }

    echo json_encode(['success' => true, 'data' => $clientes]);
}

function listarCondicionesPago($conexion) {
    $query = "SELECT id_condiciones_pago, nombre, dias_plazo FROM condicion_pago WHERE activo = 1 ORDER BY nombre";
    $result = $conexion->query($query);
    $condiciones = [];

    while($row = $result->fetch_assoc()) {
        $condiciones[] = $row;
    }

    echo json_encode(['success' => true, 'data' => $condiciones]);
}

function listarProductos($conexion) {
    $query = "SELECT id_productos, sku, nombre, precio_venta, stock 
              FROM producto 
              WHERE activo = 1 AND stock > 0 
              ORDER BY nombre";
    $result = $conexion->query($query);
    $productos = [];

    while($row = $result->fetch_assoc()) {
        // Parsear valores numéricos para asegurar formato correcto
        $row['stock'] = floatval($row['stock']);
        $row['precio_venta'] = floatval($row['precio_venta']);
        $productos[] = $row;
    }

    echo json_encode(['success' => true, 'data' => $productos]);
}

function generarNumeroDocumento($conexion) {
    // Obtener el último número de factura
    $query = "SELECT numero_documento FROM factura ORDER BY id_facturas DESC LIMIT 1";
    $result = $conexion->query($query);
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $ultimo_numero = $row['numero_documento'];
        
        // Extraer el número y aumentarlo
        if (preg_match('/FAC-(\d+)/', $ultimo_numero, $matches)) {
            $numero = intval($matches[1]) + 1;
        } else {
            $numero = 1;
        }
    } else {
        $numero = 1;
    }
    
    $nuevo_numero = 'FAC-' . str_pad($numero, 8, '0', STR_PAD_LEFT);
    
    echo json_encode(['success' => true, 'numero' => $nuevo_numero]);
}

function cargarDesdeCotizacion($conexion, $id) {
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'ID cotización requerido']);
        return;
    }
    $id = intval($id);

    $stmt = $conexion->prepare("SELECT c.*, cli.nombre AS cliente_nombre FROM cotizacion c INNER JOIN cliente cli ON cli.id_clientes = c.cliente_id WHERE c.id_cotizaciones = ? LIMIT 1");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Cotización no encontrada']);
        return;
    }
    $cot = $res->fetch_assoc();
    if ((int)$cot['activo'] === 0) {
        echo json_encode(['success' => false, 'message' => 'Cotización ya convertida o anulada']);
        return;
    }

    $stmtDet = $conexion->prepare("SELECT cd.*, p.nombre AS producto_nombre, p.precio_venta, p.stock FROM cotizacion_detalle cd INNER JOIN producto p ON p.id_productos = cd.producto_id WHERE cd.cotizacion_id = ?");
    $stmtDet->bind_param("i", $id);
    $stmtDet->execute();
    $detRes = $stmtDet->get_result();
    $detalle = [];
    while ($row = $detRes->fetch_assoc()) {
        $cantidad = floatval($row['cantidad']);
        $precioUnit = floatval($row['precio_unitario']);
        $subtotal = $cantidad * $precioUnit;
        $detalle[] = [
            'producto_id' => (int)$row['producto_id'],
            'nombre' => $row['producto_nombre'],
            'cantidad' => $cantidad,
            'precio_unitario' => $precioUnit,
            'precio_actual' => floatval($row['precio_venta']),
            'stock' => floatval($row['stock']),
            'subtotal' => $subtotal
        ];
    }

    echo json_encode(['success' => true, 'data' => [
        'cotizacion' => $cot,
        'detalle' => $detalle
    ]]);
}
?>
