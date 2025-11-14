<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: index.html");
    exit();
}
$usuario = $_SESSION['usuario'];

require_once 'conexion.php';
include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="main-content">
    <div class="main-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col">
                    <h4 class="page-title">
                        <i class="fa-solid fa-file-invoice-dollar me-2"></i>Cuentas por Cobrar (CxC)
                    </h4>
                    <p class="page-subtitle">Administración de clientes, cobros y reportes financieros</p>
                </div>

                <div class="user-info">
                    <div class="user-avatar"><?php echo strtoupper(substr($usuario, 0, 1)); ?></div>
                        <div class="user-details">
                <div class="username"><?php echo $usuario; ?></div>
            <div class="role">Usuario del Sistema</div>
        </div>
</div>

            </div>
        </div>
    </div>

    <div class="content-area">
        <div class="row g-4">

            <!-- MANTENIMIENTOS -->
            <div class="col-md-3">
                <a href="MantCliente.php" class="card-dashboard">
                    <i class="fa-solid fa-user-group"></i>
                    <h5>Clientes</h5>
                    <p>Administración de clientes registrados</p>
                </a>
            </div>

            <!-- PROCESOS -->
            <div class="col-md-3">
                <a href="CxC/MantCobro.php" class="card-dashboard">
                    <i class="fa-solid fa-money-bill-transfer"></i>
                    <h5>Cobro</h5>
                    <p>Registro de pagos y transacciones</p>
                </a>
            </div>

            <!-- CONSULTAS -->
            <div class="col-md-3">
                <a href="CxC/ConsultaClientes.php" class="card-dashboard">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <h5>Consulta Clientes</h5>
                    <p>Búsqueda y verificación de información</p>
                </a>
            </div>

            <div class="col-md-3">
                <a href="CxC/ConsultaCobros.php" class="card-dashboard">
                    <i class="fa-solid fa-list-check"></i>
                    <h5>Consulta Cobros</h5>
                    <p>Historial y detalles de cobros</p>
                </a>
            </div>

            <!-- REPORTES -->
            <div class="col-md-3">
                <a href="CxC/ReporteClientes.php" class="card-dashboard">
                    <i class="fa-solid fa-chart-column"></i>
                    <h5>Reporte de Clientes</h5>
                    <p>Análisis y listado general</p>
                </a>
            </div>

            <div class="col-md-3">
                <a href="CxC/ReporteCobros.php" class="card-dashboard">
                    <i class="fa-solid fa-file-lines"></i>
                    <h5>Reporte de Cobros</h5>
                    <p>Resumen de pagos recibidos</p>
                </a>
            </div>

        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
