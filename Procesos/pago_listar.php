<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: ../index.html");
    exit();
}

require "../conexion.php";
include "../includes/header.php";
include "../includes/sidebar.php";
?>

<div class="main-content">
    <div class="container-fluid">

        <div class="d-flex justify-content-between align-items-center mt-3 mb-3">
            <h4><i class="fa-solid fa-wallet me-2"></i>Listado de Pagos</h4>
            <a href="MantPago.php" class="btn btn-primary">
                <i class="fa-solid fa-plus me-2"></i>Nuevo Pago
            </a>
        </div>

        <div class="card">
            <div class="card-body">

                <table class="table table-bordered" id="tablaPagos">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Número</th>
                            <th>Proveedor</th>
                            <th>Fecha</th>
                            <th>Monto</th>
                            <th>Usuario</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT p.id_pagos, p.numero_documento, p.fecha, p.monto,
                                       pr.nombre AS proveedor,
                                       u.nombre AS usuario
                                FROM pago p
                                INNER JOIN proveedor pr ON pr.id_proveedores = p.proveedor_id
                                INNER JOIN usuario u ON u.id_usuarios = p.usuario_id
                                ORDER BY p.id_pagos ASC";

                        $res = $conexion->query($sql);

                        while ($row = $res->fetch_assoc()) {
                            echo "
                                <tr>
                                    <td>{$row['id_pagos']}</td>
                                    <td>{$row['numero_documento']}</td>
                                    <td>{$row['proveedor']}</td>
                                    <td>{$row['fecha']}</td>
                                    <td>$" . number_format($row['monto'], 2) . "</td>
                                    <td>{$row['usuario']}</td>
                                    <td>
                                        <a href='Pago_imprimir.php?id={$row['id_pagos']}' 
                                           class='btn btn-sm btn-primary' target='_blank'>
                                            <i class='fa-solid fa-print'></i>
                                        </a>
                                    </td>
                                </tr>
                            ";
                        }
                        ?>
                    </tbody>
                </table>

            </div>
        </div>

    </div>
</div>

<?php include "../includes/footer.php"; ?>
