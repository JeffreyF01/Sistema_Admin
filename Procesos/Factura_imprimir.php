<?php
session_start();
if(!isset($_SESSION['usuario'])) {
    header("Location: ../index.html");
    exit();
}
require_once '../conexion.php';

// Obtener ID de factura
$factura_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($factura_id <= 0) {
    die('ID de factura no válido');
}

// Obtener datos de la factura
$query = "SELECT f.*, c.nombre as cliente_nombre, c.doc_identidad, c.direccion, c.telefono, c.email,
          cp.nombre as condicion_nombre, cp.dias_plazo, u.nombre as usuario_nombre
          FROM factura f
          INNER JOIN cliente c ON f.cliente_id = c.id_clientes
          INNER JOIN condicion_pago cp ON f.condicion_id = cp.id_condiciones_pago
          INNER JOIN usuario u ON f.usuario_id = u.id_usuarios
          WHERE f.id_facturas = ?";

$stmt = $conexion->prepare($query);
$stmt->bind_param("i", $factura_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die('Factura no encontrada');
}

$factura = $result->fetch_assoc();

// Obtener detalle de la factura
$query_detalle = "SELECT fd.*, p.nombre as producto_nombre, p.sku
                  FROM factura_detalle fd
                  INNER JOIN producto p ON fd.producto_id = p.id_productos
                  WHERE fd.factura_id = ? AND fd.activo = 1
                  ORDER BY fd.id_factura_detalle";

$stmt_detalle = $conexion->prepare($query_detalle);
$stmt_detalle->bind_param("i", $factura_id);
$stmt_detalle->execute();
$result_detalle = $stmt_detalle->get_result();

// Obtener datos de la empresa
$query_empresa = "SELECT * FROM empresa LIMIT 1";
$result_empresa = $conexion->query($query_empresa);
$empresa = $result_empresa->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura <?php echo htmlspecialchars($factura['numero_documento']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { margin: 0; padding: 0; }
            @page { margin: 1cm; }
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            padding: 20px;
        }
        
        .invoice-container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        .invoice-header {
            border-bottom: 3px solid #007bff;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .company-info {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .company-info h2 {
            color: #007bff;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .invoice-details {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .invoice-table th {
            background-color: #007bff;
            color: white;
            padding: 12px;
        }
        
        .invoice-table td {
            padding: 10px;
            vertical-align: middle;
        }
        
        .invoice-total {
            background-color: #007bff;
            color: white;
            font-size: 1.3em;
            font-weight: bold;
            padding: 15px;
        }
        
        .footer-note {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #dee2e6;
            text-align: center;
            color: #6c757d;
            font-size: 0.9em;
        }
        
        .btn-print {
            position: fixed;
            bottom: 30px;
            right: 30px;
            padding: 15px 30px;
            font-size: 16px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.2);
        }
        
        .anulado-watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 120px;
            color: rgba(220, 53, 69, 0.2);
            font-weight: bold;
            z-index: -1;
            pointer-events: none;
        }
        
        .status-badge {
            display: inline-block;
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: 500;
            font-size: 14px;
        }
        
        .status-activo {
            background-color: #d1e7dd;
            color: #0f5132;
        }
        
        .status-anulado {
            background-color: #f8d7da;
            color: #842029;
        }
    </style>
</head>
<body>
    <div class="invoice-container position-relative">
        <?php if ($factura['activo'] == 0): ?>
            <div class="anulado-watermark">ANULADO</div>
        <?php endif; ?>
        
        <!-- Header -->
        <div class="invoice-header">
            <div class="company-info">
                <?php if ($empresa): ?>
                    <h2><?php echo htmlspecialchars($empresa['nombre']); ?></h2>
                    <?php if ($empresa['rnc']): ?>
                        <p class="mb-1"><strong>RNC:</strong> <?php echo htmlspecialchars($empresa['rnc']); ?></p>
                    <?php endif; ?>
                    <?php if ($empresa['direccion']): ?>
                        <p class="mb-1"><?php echo htmlspecialchars($empresa['direccion']); ?></p>
                    <?php endif; ?>
                    <?php if ($empresa['telefono']): ?>
                        <p class="mb-1"><strong>Tel:</strong> <?php echo htmlspecialchars($empresa['telefono']); ?></p>
                    <?php endif; ?>
                    <?php if ($empresa['email']): ?>
                        <p class="mb-0"><strong>Email:</strong> <?php echo htmlspecialchars($empresa['email']); ?></p>
                    <?php endif; ?>
                <?php else: ?>
                    <h2>SISTEMA DE ADMINISTRACIÓN</h2>
                    <p>Factura de Venta</p>
                <?php endif; ?>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <h4>FACTURA</h4>
                    <p class="mb-1"><strong>N°:</strong> <?php echo htmlspecialchars($factura['numero_documento']); ?></p>
                    <p class="mb-1"><strong>Fecha:</strong> <?php echo date('d/m/Y', strtotime($factura['fecha'])); ?></p>
                    <p class="mb-0">
                        <strong>Estado:</strong> 
                        <span class="status-badge <?php echo $factura['activo'] == 1 ? 'status-activo' : 'status-anulado'; ?>">
                            <?php echo $factura['activo'] == 1 ? 'ACTIVO' : 'ANULADO'; ?>
                        </span>
                    </p>
                </div>
                <div class="col-md-6 text-end">
                    <h5>CLIENTE</h5>
                    <p class="mb-1"><strong><?php echo htmlspecialchars($factura['cliente_nombre']); ?></strong></p>
                    <?php if ($factura['doc_identidad']): ?>
                        <p class="mb-1"><strong>Doc:</strong> <?php echo htmlspecialchars($factura['doc_identidad']); ?></p>
                    <?php endif; ?>
                    <?php if ($factura['direccion']): ?>
                        <p class="mb-1"><?php echo htmlspecialchars($factura['direccion']); ?></p>
                    <?php endif; ?>
                    <?php if ($factura['telefono']): ?>
                        <p class="mb-1"><strong>Tel:</strong> <?php echo htmlspecialchars($factura['telefono']); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Invoice Details -->
        <div class="invoice-details">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-1"><strong>Condición de Pago:</strong> <?php echo htmlspecialchars($factura['condicion_nombre']); ?> (<?php echo $factura['dias_plazo']; ?> días)</p>
                </div>
                <div class="col-md-6">
                    <p class="mb-1"><strong>Atendido por:</strong> <?php echo htmlspecialchars($factura['usuario_nombre']); ?></p>
                </div>
            </div>
        </div>
        
        <!-- Items Table -->
        <table class="table table-bordered invoice-table">
            <thead>
                <tr>
                    <th style="width: 15%;">SKU</th>
                    <th style="width: 40%;">Descripción</th>
                    <th style="width: 15%;" class="text-center">Cantidad</th>
                    <th style="width: 15%;" class="text-end">Precio Unit.</th>
                    <th style="width: 15%;" class="text-end">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $subtotal_general = 0;
                while($item = $result_detalle->fetch_assoc()): 
                    $subtotal_item = $item['cantidad'] * $item['precio_unitario'];
                    $subtotal_general += $subtotal_item;
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['sku']); ?></td>
                    <td><?php echo htmlspecialchars($item['producto_nombre']); ?></td>
                    <td class="text-center"><?php echo number_format($item['cantidad'], 2); ?></td>
                    <td class="text-end">$<?php echo number_format($item['precio_unitario'], 2); ?></td>
                    <td class="text-end">$<?php echo number_format($subtotal_item, 2); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" class="text-end invoice-total">TOTAL:</td>
                    <td class="text-end invoice-total">$<?php echo number_format($factura['total'], 2); ?></td>
                </tr>
            </tfoot>
        </table>
        
        <!-- Footer -->
        <div class="footer-note">
            <p class="mb-1"><strong>¡Gracias por su compra!</strong></p>
            <p class="mb-0">Este documento fue generado electrónicamente el <?php echo date('d/m/Y H:i:s'); ?></p>
        </div>
    </div>
    
    <!-- Print Button -->
    <button class="btn btn-primary btn-lg btn-print no-print" onclick="window.print()">
        <i class="fas fa-print"></i> Imprimir
    </button>
    
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <script>
        // Auto print on load (opcional)
        // window.onload = function() { window.print(); };
    </script>
</body>
</html>
