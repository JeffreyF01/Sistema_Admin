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
                        <i class="fa-solid fa-file-invoice-dollar me-2"></i>Cuentas por Pagar (CxP)
                    </h4>
                    <p class="page-subtitle">Administración de proveedores y pagos</p>
                </div>
                <div class="col-auto d-flex align-items-center gap-3">
                    <div class="user-info">
                        <div class="user-avatar"><?php echo strtoupper(substr($usuario, 0, 1)); ?></div>
                        <div class="user-details">
                            <div class="username"><?php echo $usuario; ?></div>
                            <div class="role">Administrador</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="content-area">
        <div class="row g-4">

            <!-- MANTENIMIENTOS -->
            <div class="col-md-3">
                <a href="MantProveedor.php" class="card-dashboard">
                    <i class="fa-solid fa-user-group"></i>
                    <h5>Proveedores</h5>
                    <p>Administración de clientes registrados</p>
                </a>
            </div>

            <!-- PROCESOS -->
            <div class="col-md-3">
                <a href="Procesos/MantPago.php" class="card-dashboard">
                    <i class="fa-solid fa-money-bill-transfer"></i>
                    <h5>Pago</h5>
                    <p>Registro de pagos y transacciones</p>
                </a>
            </div>

        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
