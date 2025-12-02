<?php
require "../conexion.php";

$id = intval($_GET['id']);

// ===============================
// 1. OBTENER PAGO PRINCIPAL
// ===============================
$sql = "SELECT p.*, 
               pr.nombre AS proveedor,
               u.nombre AS usuario
        FROM pago p
        INNER JOIN proveedor pr ON pr.id_proveedores = p.proveedor_id
        INNER JOIN usuario u ON u.id_usuarios = p.usuario_id
        WHERE p.id_pagos = ?";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$pago = $stmt->get_result()->fetch_assoc();

if (!$pago) {
    die("Pago no encontrado");
}

// ===============================
// 2. OBTENER DETALLES
// ===============================
$sql = "SELECT pd.*, c.numero_documento, c.fecha, c.total
        FROM pago_detalle pd
        INNER JOIN compra c ON c.id_compras = pd.compra_id
        WHERE pd.pago_id = ?";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$detalles = $stmt->get_result();

?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Pago <?= $pago['numero_documento'] ?></title>
<style>
body { font-family: Arial; margin: 30px; }
table { width: 100%; border-collapse: collapse; margin-top: 20px; }
th, td { border: 1px solid #ddd; padding: 8px; }
th { background: #f3f3f3; }
h2 { margin-bottom: 0; }
</style>
</head>
<body>

<h2>Comprobante de Pago</h2>
<p><strong>N° Pago:</strong> <?= $pago['numero_documento'] ?></p>
<p><strong>Proveedor:</strong> <?= $pago['proveedor'] ?></p>
<p><strong>Fecha:</strong> <?= $pago['fecha'] ?></p>
<p><strong>Método:</strong> <?= $pago['metodo_pago'] ?></p>
<p><strong>Usuario:</strong> <?= $pago['usuario'] ?></p>
<p><strong>Monto Total:</strong> $<?= number_format($pago['monto'], 2) ?></p>

<h3>Facturas Aplicadas</h3>

<table>
<thead>
    <tr>
        <th>Factura</th>
        <th>Fecha</th>
        <th>Total</th>
        <th>Monto Aplicado</th>
    </tr>
</thead>
<tbody>
<?php
while ($row = $detalles->fetch_assoc()) {
    echo "
        <tr>
            <td>{$row['numero_documento']}</td>
            <td>{$row['fecha']}</td>
            <td>$" . number_format($row['total'], 2) . "</td>
            <td>$" . number_format($row['monto_aplicado'], 2) . "</td>
        </tr>";
}
?>
</tbody>
</table>

<script>
window.print();
</script>

</body>
</html>
