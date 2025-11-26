<?php
session_start();
if(!isset($_SESSION['usuario'])) {
    header("Location: ../index.html");
    exit();
}
require_once '../conexion.php';

// Obtener ID de compra
$compra_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($compra_id <= 0) {
    die('ID de compra no válido');
}

// 📌 Obtener datos de la compra
$query = "SELECT c.*, p.nombre AS proveedor_nombre, p.rnc, p.direccion, p.telefono, p.email,
                 u.nombre AS usuario_nombre
          FROM compra c
          INNER JOIN proveedor p ON c.proveedor_id = p.id_proveedores
          INNER JOIN usuario u ON c.usuario_id = u.id_usuarios
          WHERE c.id_compras = ?";

$stmt = $conexion->prepare($query);
$stmt->bind_param("i", $compra_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die('Compra no encontrada');
}

$compra = $result->fetch_assoc();

// 📌 Obtener detalle de compra
$query_detalle = "SELECT cd.*, pr.nombre AS producto_nombre, pr.sku
                  FROM compra_detalle cd
                  INNER JOIN producto pr ON cd.producto_id = pr.id_productos
                  WHERE cd.compra_id = ? AND cd.activo = 1
                  ORDER BY cd.id_compra_detalle";

$stmt_detalle = $conexion->prepare($query_detalle);
$stmt_detalle->bind_param("i", $compra_id);
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
    <title>Compra <?php echo htmlspecialchars($compra['numero_documento']); ?></title>
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
            border-bottom: 3px solid #007bff;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .company-info h2 {
            color: #007bff;
            font-weight: bold;
        }

        .invoice-table th {
            background: #007bff;
            color: #fff;
        }

        .invoice-total {
            background: #007bff;
            color: white;
            padding: 15px;
            font-size: 1.3em;
        }

        .anulado-watermark {
            position: absolute;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 120px;
            color: rgba(220, 53, 69, 0.2);
            font-weight: bold;
        }

        .status-badge {
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 14px;
        }

        .status-activo {
            background: #d1e7dd;
            color: #0f5132;
        }

        .status-anulado {
            background: #f8d7da;
            color: #842029;
        }
    </style>
</head>

<body>
<div class="invoice-container position-relative">

    <!-- Marca de agua -->
    <?php if ($compra['activo'] == 0): ?>
        <div class="anulado-watermark">ANULADO</div>
    <?php endif; ?>

    <!-- HEADER -->
    <div class="invoice-header">
        <div class="company-info text-center">
            <?php if ($empresa): ?>
                <h2><?php echo htmlspecialchars($empresa['nombre']); ?></h2>
                <?php if ($empresa['rnc']): ?><p><strong>RNC:</strong> <?php echo $empresa['rnc']; ?></p><?php endif; ?>
                <?php if ($empresa['direccion']): ?><p><?php echo $empresa['direccion']; ?></p><?php endif; ?>
                <?php if ($empresa['telefono']): ?><p><strong>Tel:</strong> <?php echo $empresa['telefono']; ?></p><?php endif; ?>
            <?php endif; ?>
        </div>

        <div class="row mt-3">
            <div class="col-md-6">
                <h4>COMPRA</h4>
                <p><strong>N°:</strong> <?php echo $compra['numero_documento']; ?></p>
                <p><strong>Fecha:</strong> <?php echo date('d/m/Y', strtotime($compra['fecha'])); ?></p>

                <p>
                    <strong>Estado:</strong>
                    <span class="status-badge <?php echo $compra['activo'] ? 'status-activo' : 'status-anulado'; ?>">
                        <?php echo $compra['activo'] ? 'ACTIVO' : 'ANULADO'; ?>
                    </span>
                </p>
            </div>

            <div class="col-md-6 text-end">
                <h5>PROVEEDOR</h5>
                <p><strong><?php echo $compra['proveedor_nombre']; ?></strong></p>

                <?php if ($compra['rnc']): ?>
                    <p><strong>RNC:</strong> <?php echo $compra['rnc']; ?></p>
                <?php endif; ?>

                <?php if ($compra['direccion']): ?>
                    <p><?php echo $compra['direccion']; ?></p>
                <?php endif; ?>

                <?php if ($compra['telefono']): ?>
                    <p><strong>Tel:</strong> <?php echo $compra['telefono']; ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- DETALLES -->
    <div class="invoice-details mb-3">
        <p><strong>Procesada por:</strong> <?php echo $compra['usuario_nombre']; ?></p>
    </div>

    <!-- TABLA -->
    <table class="table table-bordered invoice-table">
        <thead>
            <tr>
                <th>SKU</th>
                <th>Descripción</th>
                <th class="text-center">Cantidad</th>
                <th class="text-end">Costo Unit.</th>
                <th class="text-end">Subtotal</th>
            </tr>
        </thead>

        <tbody>
        <?php
        $total_general = 0;
        while ($item = $detalle->fetch_assoc()):
            $subtotal = $item['cantidad'] * $item['costo_unitario'];
            $total_general += $subtotal;
        ?>
            <tr>
                <td><?php echo $item['sku']; ?></td>
                <td><?php echo $item['producto_nombre']; ?></td>
                <td class="text-center"><?php echo number_format($item['cantidad'], 2); ?></td>
                <td class="text-end">$<?php echo number_format($item['costo_unitario'], 2); ?></td>
                <td class="text-end">$<?php echo number_format($subtotal, 2); ?></td>
            </tr>
        <?php endwhile; ?>
        </tbody>

        <tfoot>
        <tr>
            <td colspan="4" class="text-end invoice-total">TOTAL:</td>
            <td class="text-end invoice-total">
                $<?php echo number_format($total_general, 2); ?>
            </td>
        </tr>
        </tfoot>
    </table>

    <div class="footer-note text-center mt-4">
        <p><strong>Documento generado automáticamente el <?php echo date('d/m/Y H:i:s'); ?></strong></p>
    </div>
</div>

<button onclick="window.print()" class="btn btn-primary btn-lg no-print" style="position:fixed; bottom:30px; right:30px;">
    <i class="fas fa-print"></i> Imprimir
</button>
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

</body>
</html>
