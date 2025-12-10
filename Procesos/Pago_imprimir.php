<?php
session_start();
if(!isset($_SESSION['usuario'])) {
    header("Location: ../index.html");
    exit();
}
require_once '../conexion.php';

// Obtener ID de pago
$pago_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($pago_id <= 0) {
    die('ID de pago no válido');
}

// 📌 Obtener datos del pago
$query = "SELECT p.*, pr.nombre AS proveedor_nombre, pr.rnc, pr.direccion, pr.telefono, pr.email,
                 u.nombre AS usuario_nombre
          FROM pago p
          INNER JOIN proveedor pr ON p.proveedor_id = pr.id_proveedores
          INNER JOIN usuario u ON p.usuario_id = u.id_usuarios
          WHERE p.id_pagos = ?";

$stmt = $conexion->prepare($query);
$stmt->bind_param("i", $pago_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die('Pago no encontrado');
}

$pago = $result->fetch_assoc();

// 📌 Obtener detalle del pago
$query_detalle = "SELECT pd.*, c.numero_documento, c.fecha, c.total
                  FROM pago_detalle pd
                  INNER JOIN compra c ON c.id_compras = pd.compra_id
                  WHERE pd.pago_id = ? AND pd.activo = 1
                  ORDER BY pd.id_pago_detalle";

$stmt_detalle = $conexion->prepare($query_detalle);
$stmt_detalle->bind_param("i", $pago_id);
$stmt_detalle->execute();
$detalle = $stmt_detalle->get_result();

// 📌 Datos de la empresa
$query_empresa = "SELECT * FROM empresa LIMIT 1";
$result_empresa = $conexion->query($query_empresa);
$empresa = $result_empresa->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pago <?php echo htmlspecialchars($pago['numero_documento']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { margin: 0; padding: 0; }
            @page { margin: 1cm; }
        }

        body {
            font-family: 'Segoe UI', Tahoma, sans-serif;
            background-color: #f5f5f5;
            padding: 20px;
        }

        .invoice-container {
            max-width: 900px;
            margin: auto;
            background: white;
            padding: 40px;
            box-shadow: 0px 0px 10px rgba(0,0,0,0.1);
        }

        .invoice-header {
            border-bottom: 3px solid #28a745;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .company-info h2 {
            color: #28a745;
            font-weight: bold;
        }

        .invoice-table th {
            background: #28a745;
            color: #fff;
        }

        .invoice-total {
            background: #28a745;
            color: white;
            padding: 15px;
            font-size: 1.3em;
            border-radius: 5px;
            text-align: right;
        }

        .info-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .print-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 1000;
        }

        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 120px;
            color: rgba(40, 167, 69, 0.1);
            z-index: -1;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="watermark">PAGO</div>

    <div class="invoice-container">
        <!-- Encabezado -->
        <div class="invoice-header">
            <div class="row">
                <div class="col-md-6">
                    <div class="company-info">
                        <h2><?php echo htmlspecialchars($empresa['nombre'] ?? 'Sistema Admin'); ?></h2>
                        <?php if($empresa): ?>
                        <p class="mb-0"><strong>RNC:</strong> <?php echo htmlspecialchars($empresa['rnc'] ?? ''); ?></p>
                        <p class="mb-0"><strong>Teléfono:</strong> <?php echo htmlspecialchars($empresa['telefono'] ?? ''); ?></p>
                        <p class="mb-0"><strong>Email:</strong> <?php echo htmlspecialchars($empresa['email'] ?? ''); ?></p>
                        <p class="mb-0"><strong>Dirección:</strong> <?php echo htmlspecialchars($empresa['direccion'] ?? ''); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-md-6 text-end">
                    <h3 class="text-success">COMPROBANTE DE PAGO</h3>
                    <p class="mb-1"><strong>N° Documento:</strong> <?php echo htmlspecialchars($pago['numero_documento']); ?></p>
                    <p class="mb-1"><strong>Fecha:</strong> <?php echo date('d/m/Y', strtotime($pago['fecha'])); ?></p>
                    <p class="mb-1"><strong>Usuario:</strong> <?php echo htmlspecialchars($pago['usuario_nombre']); ?></p>
                </div>
            </div>
        </div>

        <!-- Información del Proveedor -->
        <div class="info-box">
            <h5 class="text-success mb-3">Información del Proveedor</h5>
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-1"><strong>Nombre:</strong> <?php echo htmlspecialchars($pago['proveedor_nombre']); ?></p>
                    <p class="mb-1"><strong>RNC:</strong> <?php echo htmlspecialchars($pago['rnc'] ?? 'N/A'); ?></p>
                    <p class="mb-1"><strong>Teléfono:</strong> <?php echo htmlspecialchars($pago['telefono'] ?? 'N/A'); ?></p>
                </div>
                <div class="col-md-6">
                    <p class="mb-1"><strong>Email:</strong> <?php echo htmlspecialchars($pago['email'] ?? 'N/A'); ?></p>
                    <p class="mb-1"><strong>Dirección:</strong> <?php echo htmlspecialchars($pago['direccion'] ?? 'N/A'); ?></p>
                    <p class="mb-1"><strong>Método de Pago:</strong> <?php echo htmlspecialchars($pago['metodo_pago'] ?? 'Efectivo'); ?></p>
                </div>
            </div>
        </div>

        <!-- Tabla de Detalle -->
        <h5 class="mb-3">Facturas Aplicadas</h5>
        <table class="table table-bordered invoice-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Número Factura</th>
                    <th>Fecha Factura</th>
                    <th class="text-end">Total Factura</th>
                    <th class="text-end">Monto Aplicado</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $total_aplicado = 0;
                $contador = 1;
                while ($item = $detalle->fetch_assoc()): 
                    $total_aplicado += $item['monto_aplicado'];
                ?>
                <tr>
                    <td><?php echo $contador++; ?></td>
                    <td><?php echo htmlspecialchars($item['numero_documento']); ?></td>
                    <td><?php echo date('d/m/Y', strtotime($item['fecha'])); ?></td>
                    <td class="text-end">$<?php echo number_format($item['total'], 2); ?></td>
                    <td class="text-end">$<?php echo number_format($item['monto_aplicado'], 2); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <!-- Total -->
        <div class="row mt-4">
            <div class="col-md-8"></div>
            <div class="col-md-4">
                <div class="invoice-total">
                    <strong>TOTAL PAGADO: $<?php echo number_format($pago['monto'], 2); ?></strong>
                </div>
            </div>
        </div>

        <!-- Firmas -->
        <div class="row mt-5">
            <div class="col-md-6 text-center">
                <div style="border-top: 1px solid #000; display: inline-block; padding-top: 5px; min-width: 200px;">
                    Firma del Proveedor
                </div>
            </div>
            <div class="col-md-6 text-center">
                <div style="border-top: 1px solid #000; display: inline-block; padding-top: 5px; min-width: 200px;">
                    Firma Autorizada
                </div>
            </div>
        </div>
    </div>

    <!-- Botón Imprimir -->
    <button class="btn btn-success btn-lg print-btn no-print" onclick="window.print()">
        <i class="bi bi-printer"></i> Imprimir
    </button>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
