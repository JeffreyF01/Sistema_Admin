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
            guardarDevolucion($conexion, $data, $usuario_id);
            break;
        case 'listar':
            listarDevoluciones($conexion);
            break;
        case 'obtener':
            obtenerDevolucion($conexion, $data['id']);
            break;
        case 'anular':
            anularDevolucion($conexion, $data['id']);
            break;
        case 'listar_facturas':
            listarFacturas($conexion);
            break;
        case 'obtener_factura':
            obtenerFactura($conexion, $data['id']);
            break;
        case 'generar_numero':
            generarNumeroDocumento($conexion);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

function guardarDevolucion($conexion, $data, $usuario_id) {
    try {
        $conexion->begin_transaction();

        $numero_documento = $data['numero_documento'];
        $factura_id = $data['factura_id'];
        $fecha = $data['fecha'];
        $detalle = $data['detalle'];

        // Verificar que la factura existe y está activa
        $query_factura = "SELECT * FROM factura WHERE id_facturas = ? AND activo = 1";
        $stmt_factura = $conexion->prepare($query_factura);
        $stmt_factura->bind_param("i", $factura_id);
        $stmt_factura->execute();
        $result_factura = $stmt_factura->get_result();
        
        if ($result_factura->num_rows == 0) {
            throw new Exception("Factura no encontrada o ya anulada");
        }
        
        $factura_data = $result_factura->fetch_assoc();

        // Calcular total
        $total = 0;
        foreach($detalle as $item) {
            $total += $item['subtotal'];
        }

        // Insertar devolución
        $query = "INSERT INTO devolucion (numero_documento, factura_id, usuario_id, fecha, total, activo) 
                  VALUES (?, ?, ?, ?, ?, 1)";
        $stmt = $conexion->prepare($query);
        $stmt->bind_param("siisd", $numero_documento, $factura_id, $usuario_id, $fecha, $total);
        
        if (!$stmt->execute()) {
            throw new Exception("Error al guardar la devolución: " . $stmt->error);
        }

        $devolucion_id = $conexion->insert_id;

        // Insertar detalle, actualizar inventario y crear saldo a favor
        $query_detalle = "INSERT INTO devolucion_detalle (devolucion_id, producto_id, cantidad, precio_unitario, activo) 
                          VALUES (?, ?, ?, ?, 1)";
        $stmt_detalle = $conexion->prepare($query_detalle);

        $query_update_stock = "UPDATE producto SET stock = stock + ? WHERE id_productos = ?";
        $stmt_stock = $conexion->prepare($query_update_stock);

        foreach($detalle as $item) {
            // Verificar que la cantidad no exceda lo facturado
            $query_check = "SELECT cantidad FROM factura_detalle WHERE factura_id = ? AND producto_id = ? AND activo = 1";
            $stmt_check = $conexion->prepare($query_check);
            $stmt_check->bind_param("ii", $factura_id, $item['producto_id']);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();
            
            if ($result_check->num_rows == 0) {
                throw new Exception("El producto ID " . $item['producto_id'] . " no pertenece a esta factura");
            }
            
            $factura_detalle = $result_check->fetch_assoc();
            $cantidad_facturada = floatval($factura_detalle['cantidad']);
            
            // Obtener cantidad ya devuelta previamente de este producto en esta factura
            $query_devuelto = "SELECT COALESCE(SUM(dd.cantidad), 0) as total_devuelto 
                               FROM devolucion_detalle dd
                               INNER JOIN devolucion d ON dd.devolucion_id = d.id_devoluciones
                               WHERE d.factura_id = ? AND dd.producto_id = ? AND d.activo = 1 AND dd.activo = 1";
            $stmt_devuelto = $conexion->prepare($query_devuelto);
            $stmt_devuelto->bind_param("ii", $factura_id, $item['producto_id']);
            $stmt_devuelto->execute();
            $result_devuelto = $stmt_devuelto->get_result();
            $row_devuelto = $result_devuelto->fetch_assoc();
            $cantidad_ya_devuelta = floatval($row_devuelto['total_devuelto']);
            
            $cantidad_devuelta_ahora = floatval($item['cantidad']);
            $cantidad_disponible = $cantidad_facturada - $cantidad_ya_devuelta;

            if ($cantidad_devuelta_ahora > $cantidad_disponible) {
                throw new Exception("No puede devolver más de lo disponible para el producto ID: " . $item['producto_id'] . 
                                  ". Facturado: $cantidad_facturada, Ya devuelto: $cantidad_ya_devuelta, Disponible: $cantidad_disponible");
            }

            // Insertar detalle de devolución
            $stmt_detalle->bind_param("iidd", $devolucion_id, $item['producto_id'], $item['cantidad'], $item['precio_unitario']);
            if (!$stmt_detalle->execute()) {
                throw new Exception("Error al guardar el detalle: " . $stmt_detalle->error);
            }

            // Actualizar stock (SUMAR porque es devolución)
            $stmt_stock->bind_param("di", $item['cantidad'], $item['producto_id']);
            if (!$stmt_stock->execute()) {
                throw new Exception("Error al actualizar el stock: " . $stmt_stock->error);
            }

            // Registrar movimiento de inventario (entrada)
            registrarMovimientoInventario($conexion, $item['producto_id'], $item['cantidad'], 
                                         $numero_documento, $fecha, $usuario_id, 'ENTRADA');
        }

        // Crear saldo a favor en cuentas por cobrar (cobro negativo)
        crearSaldoFavor($conexion, $factura_data['cliente_id'], $total, $numero_documento, $fecha, $usuario_id);

        $conexion->commit();
        echo json_encode([
            'success' => true, 
            'message' => 'Devolución registrada correctamente. Se ha generado un saldo a favor para el cliente.',
            'devolucion_id' => $devolucion_id
        ]);

    } catch (Exception $e) {
        $conexion->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function crearSaldoFavor($conexion, $cliente_id, $monto, $referencia, $fecha, $usuario_id) {
    // Generar número de documento para el cobro
    $numero_cobro = 'SAF-' . date('YmdHis') . '-' . rand(1000, 9999);
    
    // Insertar cobro con monto negativo (saldo a favor)
    $query_cobro = "INSERT INTO cobro (numero_documento, cliente_id, usuario_id, fecha, monto, metodo_pago, activo) 
                    VALUES (?, ?, ?, ?, ?, ?, 1)";
    $stmt_cobro = $conexion->prepare($query_cobro);
    $monto_negativo = -$monto; // Saldo a favor es negativo
    $metodo = 'SALDO A FAVOR - ' . $referencia;
    $stmt_cobro->bind_param("siisds", $numero_cobro, $cliente_id, $usuario_id, $fecha, $monto_negativo, $metodo);
    
    if (!$stmt_cobro->execute()) {
        throw new Exception("Error al crear saldo a favor: " . $stmt_cobro->error);
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

function listarDevoluciones($conexion) {
    $query = "SELECT d.*, f.numero_documento as factura_numero, c.nombre as cliente_nombre, u.nombre as usuario_nombre
              FROM devolucion d
              INNER JOIN factura f ON d.factura_id = f.id_facturas
              INNER JOIN cliente c ON f.cliente_id = c.id_clientes
              INNER JOIN usuario u ON d.usuario_id = u.id_usuarios
              ORDER BY d.fecha DESC, d.id_devoluciones DESC
              LIMIT 50";
    
    $result = $conexion->query($query);
    $devoluciones = [];

    while($row = $result->fetch_assoc()) {
        $devoluciones[] = $row;
    }

    echo json_encode(['success' => true, 'data' => $devoluciones]);
}

function obtenerDevolucion($conexion, $id) {
    // Obtener devolución
    $query = "SELECT d.*, f.numero_documento as factura_numero, c.nombre as cliente_nombre, u.nombre as usuario_nombre
              FROM devolucion d
              INNER JOIN factura f ON d.factura_id = f.id_facturas
              INNER JOIN cliente c ON f.cliente_id = c.id_clientes
              INNER JOIN usuario u ON d.usuario_id = u.id_usuarios
              WHERE d.id_devoluciones = ?";
    
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $devolucion = $result->fetch_assoc();

    // Obtener detalle
    $query_detalle = "SELECT dd.*, p.nombre as producto_nombre, p.sku
                      FROM devolucion_detalle dd
                      INNER JOIN producto p ON dd.producto_id = p.id_productos
                      WHERE dd.devolucion_id = ? AND dd.activo = 1";
    
    $stmt_detalle = $conexion->prepare($query_detalle);
    $stmt_detalle->bind_param("i", $id);
    $stmt_detalle->execute();
    $result_detalle = $stmt_detalle->get_result();
    
    $detalle = [];
    while($row = $result_detalle->fetch_assoc()) {
        $detalle[] = $row;
    }

    $devolucion['detalle'] = $detalle;

    echo json_encode(['success' => true, 'data' => $devolucion]);
}

function anularDevolucion($conexion, $id) {
    try {
        $conexion->begin_transaction();

        // Obtener detalle de la devolución
        $query_detalle = "SELECT producto_id, cantidad FROM devolucion_detalle WHERE devolucion_id = ? AND activo = 1";
        $stmt_detalle = $conexion->prepare($query_detalle);
        $stmt_detalle->bind_param("i", $id);
        $stmt_detalle->execute();
        $result_detalle = $stmt_detalle->get_result();

        // Revertir stock (descontar porque estamos anulando una devolución)
        $query_update_stock = "UPDATE producto SET stock = stock - ? WHERE id_productos = ?";
        $stmt_stock = $conexion->prepare($query_update_stock);

        while($item = $result_detalle->fetch_assoc()) {
            $stmt_stock->bind_param("di", $item['cantidad'], $item['producto_id']);
            $stmt_stock->execute();
        }

        // Obtener información de la devolución para anular el saldo a favor
        $query_dev = "SELECT d.*, f.cliente_id FROM devolucion d 
                      INNER JOIN factura f ON d.factura_id = f.id_facturas 
                      WHERE d.id_devoluciones = ?";
        $stmt_dev = $conexion->prepare($query_dev);
        $stmt_dev->bind_param("i", $id);
        $stmt_dev->execute();
        $result_dev = $stmt_dev->get_result();
        $devolucion_data = $result_dev->fetch_assoc();

        // Anular el saldo a favor (crear cobro positivo para compensar)
        $numero_anulacion = 'ANU-SAF-' . date('YmdHis') . '-' . rand(1000, 9999);
        $query_anular_saldo = "INSERT INTO cobro (numero_documento, cliente_id, usuario_id, fecha, monto, metodo_pago, activo) 
                               VALUES (?, ?, ?, CURDATE(), ?, ?, 1)";
        $stmt_anular_saldo = $conexion->prepare($query_anular_saldo);
        $monto_positivo = $devolucion_data['total']; // Monto positivo para compensar el negativo
        $metodo = 'ANULACIÓN DEVOLUCIÓN - ' . $devolucion_data['numero_documento'];
        $usuario_id = $_SESSION['id_usuarios'] ?? 1;
        $stmt_anular_saldo->bind_param("siids", $numero_anulacion, $devolucion_data['cliente_id'], $usuario_id, $monto_positivo, $metodo);
        $stmt_anular_saldo->execute();

        // Anular devolución
        $query_anular = "UPDATE devolucion SET activo = 0 WHERE id_devoluciones = ?";
        $stmt_anular = $conexion->prepare($query_anular);
        $stmt_anular->bind_param("i", $id);
        $stmt_anular->execute();

        // Anular detalle
        $query_anular_detalle = "UPDATE devolucion_detalle SET activo = 0 WHERE devolucion_id = ?";
        $stmt_anular_detalle = $conexion->prepare($query_anular_detalle);
        $stmt_anular_detalle->bind_param("i", $id);
        $stmt_anular_detalle->execute();

        $conexion->commit();
        echo json_encode(['success' => true, 'message' => 'Devolución anulada correctamente']);

    } catch (Exception $e) {
        $conexion->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function listarFacturas($conexion) {
    $query = "SELECT f.id_facturas, f.numero_documento, f.total, c.nombre as cliente_nombre
              FROM factura f
              INNER JOIN cliente c ON f.cliente_id = c.id_clientes
              WHERE f.activo = 1
              ORDER BY f.fecha DESC
              LIMIT 100";
    $result = $conexion->query($query);
    $facturas = [];

    while($row = $result->fetch_assoc()) {
        $facturas[] = $row;
    }

    echo json_encode(['success' => true, 'data' => $facturas]);
}

function obtenerFactura($conexion, $id) {
    // Obtener factura
    $query = "SELECT f.*, c.nombre as cliente_nombre
              FROM factura f
              INNER JOIN cliente c ON f.cliente_id = c.id_clientes
              WHERE f.id_facturas = ?";
    
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $factura = $result->fetch_assoc();

    // Obtener detalle con cantidad disponible para devolución
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
                                               AND dd.activo = 1), 0)) as cantidad_disponible
                      FROM factura_detalle fd
                      INNER JOIN producto p ON fd.producto_id = p.id_productos
                      WHERE fd.factura_id = ? AND fd.activo = 1
                      HAVING cantidad_disponible > 0";
    
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

function generarNumeroDocumento($conexion) {
    // Obtener el último número de devolución
    $query = "SELECT numero_documento FROM devolucion ORDER BY id_devoluciones DESC LIMIT 1";
    $result = $conexion->query($query);
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $ultimo_numero = $row['numero_documento'];
        
        // Extraer el número y aumentarlo
        if (preg_match('/DEV-(\d+)/', $ultimo_numero, $matches)) {
            $numero = intval($matches[1]) + 1;
        } else {
            $numero = 1;
        }
    } else {
        $numero = 1;
    }
    
    $nuevo_numero = 'DEV-' . str_pad($numero, 8, '0', STR_PAD_LEFT);
    
    echo json_encode(['success' => true, 'numero' => $nuevo_numero]);
}
?>
