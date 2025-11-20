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
                        <i class="fa-solid fa-sliders me-2"></i>Configuración del Sistema
                    </h4>
                    <p class="page-subtitle">Ajustes avanzados y mantenimientos administrativos</p>
                </div>
                <div class="col-auto">
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
            <div class="col-md-3">
                <a href="MantUsuario.php" class="card-dashboard">
                    <i class="fa-solid fa-user-gear"></i>
                    <h5>Usuarios</h5>
                    <p>Gestión de cuentas y accesos</p>
                </a>
            </div>

            <div class="col-md-3">
                <a href="MantEmpresa.php" class="card-dashboard">
                    <i class="fa-solid fa-building"></i>
                    <h5>Empresa</h5>
                    <p>Datos institucionales</p>
                </a>
            </div>

            <div class="col-md-3">
                <a href="MantRol.php" class="card-dashboard">
                    <i class="fa-solid fa-id-badge"></i>
                    <h5>Roles</h5>
                    <p>Definición de perfiles y permisos</p>
                </a>
            </div>

            <div class="col-md-3">
                <a href="MantMoneda.php" class="card-dashboard">
                    <i class="fa-solid fa-coins"></i>
                    <h5>Monedas</h5>
                    <p>Tipos de cambio y divisas</p>
                </a>
            </div>

            <div class="col-md-3">
                <a href="MantCondicionPago.php" class="card-dashboard">
                    <i class="fa-solid fa-file-circle-check"></i>
                    <h5>Condición de Pago</h5>
                    <p>Gestiona las condiciones de pago</p>
                </a>
            </div>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
