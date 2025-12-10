<?php
session_start();
if(!isset($_SESSION['usuario'])) {
    header("Location: ../index.html");
    exit();
}
require_once '../conexion.php';

$cobro_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($cobro_id <= 0) { die('ID de cobro no válido'); }

// ============================
// CABECERA DEL COBRO
// ============================
$stmt = $conexion->prepare("
    SELECT c.*, cli.nombre AS cliente_nombre, cli.doc_identidad,
           cli.direccion, cli.telefono, cli.email,
           u.usuario AS usuario_nombre
    FROM cobro c
    LEFT JOIN cliente cli ON cli.id_clientes = c.cliente_id
    LEFT JOIN usuario u ON u.id_usuarios = c.usuario_id
    WHERE c.id_cobros = ?
    LIMIT 1
");
$stmt->bind_param("i", $cobro_id);
$stmt->execute();
$res = $stmt->get_result();

if($res->num_rows === 0){ die('Cobro no encontrado'); }

$cobro = $res->fetch_assoc();

// ============================
// DETALLE DEL COBRO
// ============================
$stmtDet = $conexion->prepare("
    SELECT cd.*, f.numero_documento, f.fecha, f.total
    FROM cobro_detalle cd
    LEFT JOIN factura f ON f.id_facturas = cd.factura_id
    WHERE cd.cobro_id = ?
    ORDER BY cd.id_cobro_detalle
");
$stmtDet->bind_param("i", $cobro_id);
$stmtDet->execute();
$detRes = $stmtDet->get_result();

// ============================
// DATOS DE LA EMPRESA
// ============================
$empresa = null;
$empRes = $conexion->query("SELECT * FROM empresa LIMIT 1");
if($empRes && $empRes->num_rows > 0){ $empresa = $empRes->fetch_assoc(); }

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cobro <?php echo htmlspecialchars($cobro['numero_documento']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print { .no-print { display:none !important; } body{ margin:0; padding:0;} @page{ margin:1cm; } }
        body { font-family:'Segoe UI', Tahoma, sans-serif; background:#f5f5f5; padding:20px; }
        .doc-container { max-width:900px; margin:auto; background:#fff; padding:40px; box-shadow:0 0 10px rgba(0,0,0,.1); position:relative; }
        .header-border { border-bottom:3px solid #0d6efd; padding-bottom:20px; margin-bottom:30px; }
        .company h2 { color:#0d6efd; font-weight:700; margin-bottom:5px; }
        .status-badge { display:inline-block; padding:6px 14px; border-radius:18px; font-size:13px; font-weight:500; }
        .status-activa { background:#d1e7dd; color:#0f5132; }
        .status-inactiva { background:#e2e3e5; color:#41464b; }
        .watermark { position:absolute; top:50%; left:50%; transform:translate(-50%,-50%) rotate(-35deg); font-size:120px; color:rgba(108,117,125,.15); font-weight:700; pointer-events:none; }
        table thead th { background:#0d6efd; color:#fff; }
        .total-row td { background:#0d6efd; color:#fff; font-weight:700; }
        .footer-note { margin-top:30px; text-align:center; color:#6c757d; font-size:.9em; border-top:2px solid #dee2e6; padding-top:15px; }
        .btn-print { position:fixed; bottom:30px; right:30px; box-shadow:0 4px 6px rgba(0,0,0,.2); }
    </style>
</head>
<body>
<div class="doc-container">

    <?php if((int)$cobro['activo'] === 0): ?>
        <div class="watermark">ANULADO</div>
    <?php endif; ?>

    <div class="header-border">
        <div class="company text-center mb-3">
            <?php if($empresa): ?>
                <h2><?php echo htmlspecialchars($empresa['nombre']); ?></h2>
                <?php if($empresa['rnc']): ?><p class="mb-1"><strong>RNC:</strong> <?php echo htmlspecialchars($empresa['rnc']); ?></p><?php endif; ?>
                <?php if($empresa['direccion']): ?><p class="mb-1"><?php echo htmlspecialchars($empresa['direccion']); ?></p><?php endif; ?>
                <?php if($empresa['telefono']): ?><p class="mb-1"><strong>Tel:</strong> <?php echo htmlspecialchars($empresa['telefono']); ?></p><?php endif; ?>
                <?php if($empresa['email']): ?><p class="mb-0"><strong>Email:</strong> <?php echo htmlspecialchars($empresa['email']); ?></p><?php endif; ?>
            <?php else: ?>
                <h2>SISTEMA ADMIN</h2>
                <p class="mb-0">Comprobante de Cobro</p>
            <?php endif; ?>
        </div>

        <div class="row">
            <div class="col-md-6">
                <h4 class="mb-2">COBRO</h4>
                <p class="mb-1"><strong>N°:</strong> <?php echo htmlspecialchars($cobro['numero_documento']); ?></p>
                <p class="mb-1"><strong>Fecha:</strong> <?php echo date('d/m/Y', strtotime($cobro['fecha'])); ?></p>
                <p class="mb-1"><strong>Método:</strong> <?php echo htmlspecialchars($cobro['metodo_pago']); ?></p>
                <p class="mb-0"><strong>Estado:</strong> 
                    <span class="status-badge <?php echo (int)$cobro['activo']===1?'status-activa':'status-inactiva'; ?>">
                        <?php echo (int)$cobro['activo']===1?'ACTIVO':'ANULADO'; ?>
                    </span>
                </p>
            </div>

            <div class="col-md-6 text-end">
                <h5 class="mb-2">CLIENTE</h5>
                <p class="mb-1"><strong><?php echo htmlspecialchars($cobro['cliente_nombre']); ?></strong></p>
                <?php if($cobro['doc_identidad']): ?><p class="mb-1"><strong>Doc:</strong> <?php echo htmlspecialchars($cobro['doc_identidad']); ?></p><?php endif; ?>
                <?php if($cobro['direccion']): ?><p class="mb-1"><?php echo htmlspecialchars($cobro['direccion']); ?></p><?php endif; ?>
                <?php if($cobro['telefono']): ?><p class="mb-1"><strong>Tel:</strong> <?php echo htmlspecialchars($cobro['telefono']); ?></p><?php endif; ?>
                <?php if($cobro['email']): ?><p class="mb-0"><strong>Email:</strong> <?php echo htmlspecialchars($cobro['email']); ?></p><?php endif; ?>
            </div>
        </div>
    </div>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th style="width:20%">Factura</th>
                <th style="width:20%">Fecha</th>
                <th style="width:20%" class="text-end">Total</th>
                <th style="width:20%" class="text-end">Aplicado</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $total_aplicado = 0;
            while($it = $detRes->fetch_assoc()): 
                $ap = floatval($it['monto_aplicado']);
                $total_aplicado += $ap;
            ?>
            <tr>
                <td><?php echo htmlspecialchars($it['numero_documento']); ?></td>
                <td><?php echo date('d/m/Y', strtotime($it['fecha'])); ?></td>
                <td class="text-end">$<?php echo number_format($it['total'],2); ?></td>
                <td class="text-end">$<?php echo number_format($ap,2); ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>

        <tfoot>
            <tr class="total-row">
                <td colspan="3" class="text-end">TOTAL COBRADO:</td>
                <td class="text-end">$<?php echo number_format($total_aplicado,2); ?></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer-note">
        <p class="mb-1"><strong>Documento generado el <?php echo date('d/m/Y H:i:s'); ?></strong></p>
        <p class="mb-0">Gracias por su pago.</p>
    </div>
</div>

<button class="btn btn-primary btn-lg no-print btn-print" onclick="window.print()">
    <i class="fas fa-print"></i> Imprimir
</button>

<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</body>
</html>
