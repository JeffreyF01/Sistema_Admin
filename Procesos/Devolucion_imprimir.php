<?php
session_start();
if(!isset($_SESSION['usuario'])) {
    header("Location: ../index.html");
    exit();
}
require_once '../conexion.php';

// Obtener ID de devolución
$devolucion_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($devolucion_id <= 0) {
    die('ID de devolución no válido');
}

// Obtener datos de la devolución
$query = "SELECT d.*, f.numero_documento as factura_numero, 
          c.nombre as cliente_nombre, c.doc_identidad, c.direccion, c.telefono, c.email,
          u.nombre as usuario_nombre
          FROM devolucion d
          INNER JOIN factura f ON d.factura_id = f.id_facturas
          INNER JOIN cliente c ON f.cliente_id = c.id_clientes
          INNER JOIN usuario u ON d.usuario_id = u.id_usuarios
          WHERE d.id_devoluciones = ?";

$stmt = $conexion->prepare($query);
$stmt->bind_param("i", $devolucion_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die('Devolución no encontrada');
}

$devolucion = $result->fetch_assoc();

// Obtener detalle de la devolución
$query_detalle = "SELECT dd.*, p.nombre as producto_nombre, p.sku
                  FROM devolucion_detalle dd
                  INNER JOIN producto p ON dd.producto_id = p.id_productos
                  WHERE dd.devolucion_id = ? AND dd.activo = 1
                  ORDER BY dd.id_devolucion_detalle";

$stmt_detalle = $conexion->prepare($query_detalle);
$stmt_detalle->bind_param("i", $devolucion_id);
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
    <title>Devolución <?php echo htmlspecialchars($devolucion['numero_documento']); ?></title>
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
            border-bottom: 3px solid #dc3545;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .company-info {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .company-info h2 {
            color: #dc3545;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .invoice-details {
            background-color: #fff3cd;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #ffc107;
        }
        
        .invoice-table th {
            background-color: #dc3545;
            color: white;
            padding: 12px;
        }
        
        .invoice-table td {
            padding: 10px;
            vertical-align: middle;
        }
        
        .invoice-total {
            background-color: #dc3545;
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
        
        .devolucion-badge {
            background-color: #dc3545;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            display: inline-block;
            font-weight: bold;
            font-size: 1.2em;
        }
        
        .alert-info-custom {
            background-color: #cfe2ff;
            border-left: 4px solid #0d6efd;
            padding: 15px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="invoice-container position-relative">
        <?php if ($devolucion['activo'] == 0): ?>
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
                    <p>Nota de Devolución</p>
                <?php endif; ?>
                <div class="mt-3">
                    <span class="devolucion-badge">NOTA DE DEVOLUCIÓN</span>
                </div>
            </div>
            
            <div class="row mt-4">
                <div class="col-md-6">
                    <h4>DEVOLUCIÓN</h4>
                    <p class="mb-1"><strong>N°:</strong> <?php echo htmlspecialchars($devolucion['numero_documento']); ?></p>
                    <p class="mb-1"><strong>Fecha:</strong> <?php echo date('d/m/Y', strtotime($devolucion['fecha'])); ?></p>
                    <p class="mb-1"><strong>Factura Origen:</strong> <?php echo htmlspecialchars($devolucion['factura_numero']); ?></p>
                    <p class="mb-0">
                        <strong>Estado:</strong> 
                        <span class="status-badge <?php echo $devolucion['activo'] == 1 ? 'status-activo' : 'status-anulado'; ?>">
                            <?php echo $devolucion['activo'] == 1 ? 'ACTIVO' : 'ANULADO'; ?>
                        </span>
                    </p>
                </div>
                <div class="col-md-6 text-end">
                    <h5>CLIENTE</h5>
                    <p class="mb-1"><strong><?php echo htmlspecialchars($devolucion['cliente_nombre']); ?></strong></p>
                    <?php if ($devolucion['doc_identidad']): ?>
                        <p class="mb-1"><strong>Doc:</strong> <?php echo htmlspecialchars($devolucion['doc_identidad']); ?></p>
                    <?php endif; ?>
                    <?php if ($devolucion['direccion']): ?>
                        <p class="mb-1"><?php echo htmlspecialchars($devolucion['direccion']); ?></p>
                    <?php endif; ?>
                    <?php if ($devolucion['telefono']): ?>
                        <p class="mb-1"><strong>Tel:</strong> <?php echo htmlspecialchars($devolucion['telefono']); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Invoice Details -->
        <div class="invoice-details">
            <div class="row">
                <div class="col-md-12">
                    <p class="mb-1"><i class="fas fa-info-circle"></i> <strong>Procesado por:</strong> <?php echo htmlspecialchars($devolucion['usuario_nombre']); ?></p>
                    <p class="mb-0"><i class="fas fa-exclamation-triangle"></i> <strong>Nota:</strong> Esta devolución genera un saldo a favor para el cliente en Cuentas por Cobrar</p>
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
                    <td colspan="4" class="text-end invoice-total">TOTAL DEVOLUCIÓN:</td>
                    <td class="text-end invoice-total">$<?php echo number_format($devolucion['total'], 2); ?></td>
                </tr>
            </tfoot>
        </table>
        
        <!-- Alert Information -->
        <div class="alert-info-custom">
            <h6><i class="fas fa-info-circle"></i> Información Importante:</h6>
            <ul class="mb-0">
                <li>Los productos devueltos han sido agregados nuevamente al inventario</li>
                <li>Se ha generado un saldo a favor de <strong>$<?php echo number_format($devolucion['total'], 2); ?></strong> en la cuenta del cliente</li>
                <li>Este saldo puede ser aplicado a futuras compras o reembolsado según políticas de la empresa</li>
            </ul>
        </div>
        
        <!-- Signatures -->
        <div class="row mt-5">
            <div class="col-md-6 text-center">
                <div style="border-top: 2px solid #000; width: 250px; margin: 0 auto; padding-top: 10px;">
                    <strong>Firma del Cliente</strong>
                </div>
            </div>
            <div class="col-md-6 text-center">
                <div style="border-top: 2px solid #000; width: 250px; margin: 0 auto; padding-top: 10px;">
                    <strong>Firma Autorizada</strong>
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="footer-note">
            <p class="mb-1"><strong>Devolución procesada correctamente</strong></p>
            <p class="mb-0">Este documento fue generado electrónicamente el <?php echo date('d/m/Y H:i:s'); ?></p>
        </div>
    </div>
    
    <!-- Print Button -->
    <button class="btn btn-danger btn-lg btn-print no-print" onclick="window.print()">
        <i class="fas fa-print"></i> Imprimir
    </button>
    
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <script>
        // Auto print on load (opcional)
        // window.onload = function() { window.print(); };
    </script>
</body>
</html>
