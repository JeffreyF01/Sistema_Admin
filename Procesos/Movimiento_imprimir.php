<?php
session_start();
if(!isset($_SESSION['usuario'])) {
    header("Location: ../index.html");
    exit();
}
require_once '../conexion.php';

$mov_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($mov_id <= 0) { die('ID de movimiento no válido'); }

// Cabecera del movimiento
$sqlCab = "SELECT mi.*, tm.nombre AS tipo_nombre, tm.clase, u.nombre AS usuario_nombre
           FROM movimiento_inventario mi
           INNER JOIN tipo_movimiento tm ON tm.id_tipos_movimiento = mi.tipo_movimiento_id
           LEFT JOIN usuario u ON u.id_usuarios = mi.usuario_id
           WHERE mi.id_movimientos_inventario = ?";
$stmtCab = $conexion->prepare($sqlCab);
$stmtCab->bind_param('i', $mov_id);
$stmtCab->execute();
$resCab = $stmtCab->get_result();
if ($resCab->num_rows === 0) { die('Movimiento no encontrado'); }
$mov = $resCab->fetch_assoc();

// Detalle
$sqlDet = "SELECT mid.*, p.nombre AS producto_nombre, p.sku, p.unidad
            FROM movimiento_inventario_detalle mid
            INNER JOIN producto p ON p.id_productos = mid.producto_id
            WHERE mid.movimiento_inventario_id = ? AND mid.activo = 1
            ORDER BY mid.id_movi_invd";
$stmtDet = $conexion->prepare($sqlDet);
$stmtDet->bind_param('i', $mov_id);
$stmtDet->execute();
$detRes = $stmtDet->get_result();

// Empresa
$empresa = $conexion->query("SELECT * FROM empresa LIMIT 1")->fetch_assoc();

$esEntrada = ($mov['clase'] === 'ENTRADA');
$color = $esEntrada ? '#0d6efd' : '#dc3545';
$badge = $esEntrada ? 'ENTRADA' : 'SALIDA';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movimiento <?php echo htmlspecialchars($mov['numero_documento']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print { display:none !important; }
            body { margin:0; padding:0; }
            @page { margin: 1cm; }
        }
        body {
            font-family: 'Segoe UI', Tahoma, sans-serif;
            background-color: #f5f5f5;
            padding: 20px;
        }
        .invoice-container {
            max-width: 900px;
            margin: 0 auto;
            background: #fff;
            padding: 40px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            position: relative;
        }
        .invoice-header {
            border-bottom: 3px solid <?php echo $color; ?>;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .company-info h2 { color: <?php echo $color; ?>; font-weight: bold; }
        .invoice-table th { background: <?php echo $color; ?>; color:#fff; }
        .invoice-total { background: <?php echo $color; ?>; color:#fff; padding:12px; font-weight:bold; }
        .status-badge { padding:8px 14px; border-radius: 12px; font-size:14px; font-weight:600; }
        .badge-entrada { background:#d1e7dd; color:#0f5132; }
        .badge-salida { background:#f8d7da; color:#842029; }
    </style>
</head>
<body>
<div class="invoice-container">
    <div class="invoice-header">
        <div class="company-info text-center">
            <?php if ($empresa): ?>
                <h2><?php echo htmlspecialchars($empresa['nombre']); ?></h2>
                <?php if ($empresa['rnc']): ?><p class="mb-1"><strong>RNC:</strong> <?php echo htmlspecialchars($empresa['rnc']); ?></p><?php endif; ?>
                <?php if ($empresa['direccion']): ?><p class="mb-1"><?php echo htmlspecialchars($empresa['direccion']); ?></p><?php endif; ?>
                <?php if ($empresa['telefono']): ?><p class="mb-1"><strong>Tel:</strong> <?php echo htmlspecialchars($empresa['telefono']); ?></p><?php endif; ?>
                <?php if ($empresa['email']): ?><p class="mb-0"><strong>Email:</strong> <?php echo htmlspecialchars($empresa['email']); ?></p><?php endif; ?>
            <?php else: ?>
                <h2>SISTEMA DE ADMINISTRACIÓN</h2>
                <p class="mb-0">Movimiento de inventario</p>
            <?php endif; ?>
            <div class="mt-2">
                <span class="status-badge <?php echo $esEntrada ? 'badge-entrada' : 'badge-salida'; ?>"><?php echo $badge; ?></span>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-md-6">
                <h4>MOVIMIENTO</h4>
                <p class="mb-1"><strong>N°:</strong> <?php echo htmlspecialchars($mov['numero_documento']); ?></p>
                <p class="mb-1"><strong>Fecha:</strong> <?php echo date('d/m/Y', strtotime($mov['fecha'])); ?></p>
                <p class="mb-0"><strong>Referencia:</strong> <?php echo htmlspecialchars($mov['referencia'] ?? ''); ?></p>
            </div>
            <div class="col-md-6 text-end">
                <h5>TIPO</h5>
                <p class="mb-1"><strong><?php echo htmlspecialchars($mov['tipo_nombre']); ?></strong></p>
                <p class="mb-0"><strong>Registrado por:</strong> <?php echo htmlspecialchars($mov['usuario_nombre'] ?? ''); ?></p>
            </div>
        </div>
    </div>

    <table class="table table-bordered invoice-table">
        <thead>
            <tr>
                <th style="width: 12%;">SKU</th>
                <th style="width: 44%;">Producto</th>
                <th style="width: 12%;" class="text-center">Cantidad</th>
                <th style="width: 16%;" class="text-end">Costo Unit.</th>
                <th style="width: 16%;" class="text-end">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $total_cant = 0; $total_sub = 0;
            while($d = $detRes->fetch_assoc()):
                $cant = floatval($d['cantidad']);
                $costo = $d['costo_unitario'] !== null ? floatval($d['costo_unitario']) : 0;
                $sub = $cant * $costo;
                $total_cant += $cant;
                $total_sub += $sub;
            ?>
            <tr>
                <td><?php echo htmlspecialchars($d['sku']); ?></td>
                <td><?php echo htmlspecialchars($d['producto_nombre']); ?></td>
                <td class="text-center"><?php echo number_format($cant, 2); ?></td>
                <td class="text-end"><?php echo $costo > 0 ? '$'.number_format($costo, 2) : '-'; ?></td>
                <td class="text-end"><?php echo $costo > 0 ? '$'.number_format($sub, 2) : '-'; ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2" class="text-end"><strong>Total líneas:</strong></td>
                <td class="text-center"><strong><?php echo number_format($total_cant, 2); ?></strong></td>
                <td colspan="2" class="text-end invoice-total">Valor referencial: <?php echo $total_sub > 0 ? '$'.number_format($total_sub, 2) : '-'; ?></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer-note text-center mt-4">
        <p class="mb-1"><strong>Documento generado el <?php echo date('d/m/Y H:i:s'); ?></strong></p>
        <p class="mb-0">Este movimiento afecta existencias directamente.</p>
    </div>
</div>

<button onclick="window.print()" class="btn btn-primary btn-lg no-print" style="position:fixed; bottom:30px; right:30px;">
    <i class="fas fa-print"></i> Imprimir
</button>
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</body>
</html>
