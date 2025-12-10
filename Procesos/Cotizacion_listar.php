<?php
session_start();
if(!isset($_SESSION['usuario'])){ header("Location: ../index.html"); exit(); }
require_once '../conexion.php';
include '../includes/header.php';
include '../includes/sidebar.php';
?>
<div class="main-content">
    <div class="main-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col">
                    <h4 class="page-title"><i class="fa-solid fa-list me-2"></i>Listado de Cotizaciones</h4>
                    <p class="page-subtitle">Todas las cotizaciones registradas</p>
                </div>
            </div>
        </div>
    </div>

    <div class="content-area">
        <div class="container-fluid">
            <div class="card card-custom">
                <div class="card-header card-header-custom">
                    <h5 class="mb-0">Cotizaciones</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-mantenimiento" id="listaCot">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nº Documento</th>
                                    <th>Cliente</th>
                                    <th>Fecha</th>
                                    <th>Valida Hasta</th>
                                    <th>Total</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
<?php
$sql = "SELECT c.id_cotizaciones, c.numero_documento, cli.nombre AS cliente_nombre,
               DATE_FORMAT(c.fecha,'%Y-%m-%d') AS fecha, DATE_FORMAT(c.valida_hasta,'%Y-%m-%d') AS valida_hasta,
               c.total, c.activo
        FROM cotizacion c LEFT JOIN cliente cli ON cli.id_clientes = c.cliente_id
        ORDER BY c.id_cotizaciones ASC";
$res = $conexion->query($sql);
if($res->num_rows > 0){
    while($r = $res->fetch_assoc()){
        $estado = $r['activo']==1 ? "<span style='background-color:#d1e7dd;color:#0f5132;padding:4px 8px;border-radius:12px;font-weight:500;font-size:12px;white-space:nowrap;'><i class='fas fa-circle me-1' style='font-size:6px;color:#198754;'></i>Activo</span>"
        : "<span style='background-color:#f8d7da;color:#842029;padding:4px 8px;border-radius:12px;font-weight:500;font-size:12px;white-space:nowrap;'><i class='fas fa-circle me-1' style='font-size:6px;color:#dc3545;'></i>Anulado</span>";
        echo "<tr>
            <td>{$r['id_cotizaciones']}</td>
            <td>{$r['numero_documento']}</td>
            <td>{$r['cliente_nombre']}</td>
            <td>{$r['fecha']}</td>
            <td>{$r['valida_hasta']}</td>
            <td>$".number_format($r['total'],2)."</td>
            <td>{$estado}</td>
            <td>
                <a class='btn btn-sm btn-primary' href='Cotizacion_imprimir.php?id={$r['id_cotizaciones']}' target='_blank'><i class='fa-solid fa-print'></i></a>
                ".($r['activo']==1 ? "<a class='btn btn-sm btn-success' href='?convert={$r['id_cotizaciones']}'>Convertir</a>" : "")."
            </td>
        </tr>";
    }
} else {
    echo "<tr><td colspan='8' class='text-center'>No hay registros</td></tr>";
}
?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
