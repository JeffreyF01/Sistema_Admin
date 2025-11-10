<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: index.html");
    exit();
}
$usuario = $_SESSION['usuario'];

require_once 'conexion.php';

$total_productos = 0;
$total_almacenes = 0;
$total_movimientos = 0;
$total_usuarios = 0;

if (isset($conexion) && $conexion instanceof mysqli) {
    $productos_query = "SELECT COUNT(*) as total FROM producto";
    $result = $conexion->query($productos_query);
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $total_productos = (int)$row['total'];
    }
    $almacenes_query = "SELECT COUNT(*) as total FROM almacen";
    $result = $conexion->query($almacenes_query);
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $total_almacenes = (int)$row['total'];
    }
    $movimientos_query = "SELECT COUNT(*) as total FROM movimiento_inventario WHERE DATE(fecha) = CURDATE()";
    $result = $conexion->query($movimientos_query);
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $total_movimientos = (int)$row['total'];
    }
    $usuarios_query = "SELECT COUNT(*) as total FROM usuario";
    $result = $conexion->query($usuarios_query);
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $total_usuarios = (int)$row['total'];
    }
} else {
    error_log("Conexión a BD no disponible en dashboard");
}

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="main-content">
    <div class="main-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col">
                    <h4 class="page-title">
                        <i class="fa-solid fa-gauge-high me-2"></i>Panel de Control
                    </h4>
                    <p class="page-subtitle">Resumen general del sistema</p>
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
        <div class="welcome-box">
            <div class="welcome-icon">
                <i class="fa-solid fa-user-tie"></i>
            </div>
            <div class="welcome-text">
                <h3>¡Bienvenido <?php echo htmlspecialchars($usuario); ?>!</h3>
                <p>Panel administrativo del sistema - <?php echo date('d/m/Y'); ?></p>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-md-3">
                <a href="Mantproductos.php" class="card-dashboard">
                    <i class="fa-solid fa-toolbox"></i>
                    <h5>Mantenimientos</h5>
                    <p>Gestionar catálogos y configuraciones del sistema</p>
                </a>
            </div>
            <div class="col-md-3">
                <div class="card-dashboard">
                    <i class="fa-solid fa-gears"></i>
                    <h5>Procesos</h5>
                    <p>Operaciones y transacciones de negocio</p>
                </div>
            </div>
            <div class="col-md-3">
                <a href="Consulta_departamento.php" class="card-dashboard">
                    <i class="fa-solid fa-search"></i>
                    <h5>Consultas</h5>
                    <p>Buscar y consultar información del sistema</p>
                </a>
            </div>
            <div class="col-md-3">
                <div class="card-dashboard">
                    <i class="fa-solid fa-chart-pie"></i>
                    <h5>Reportes</h5>
                    <p>Estadísticas, análisis y reportes detallados</p>
                </div>
            </div>
        </div>

        <div class="stats-section">
            <h3 class="section-title">Estadísticas del Sistema</h3>
            <div class="row g-4">
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon blue"><i class="fa-solid fa-box"></i></div>
                        <div class="stat-info">
                            <h4><?php echo $total_productos; ?></h4>
                            <p>Productos Registrados</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon green"><i class="fa-solid fa-warehouse"></i></div>
                        <div class="stat-info">
                            <h4><?php echo $total_almacenes; ?></h4>
                            <p>Almacenes Activos</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon orange"><i class="fa-solid fa-arrows-rotate"></i></div>
                        <div class="stat-info">
                            <h4><?php echo $total_movimientos; ?></h4>
                            <p>Movimientos Hoy</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon purple"><i class="fa-solid fa-users"></i></div>
                        <div class="stat-info">
                            <h4><?php echo $total_usuarios; ?></h4>
                            <p>Usuarios Activos</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="quick-actions">
            <h3 class="section-title">Acciones Rápidas</h3>
            <div class="action-buttons">
                <a href="Mantproductos.php" class="action-btn">
                    <i class="fa-solid fa-plus"></i>
                    <span>Nuevo Producto</span>
                </a>
                <a href="MantAlmacen.php" class="action-btn">
                    <i class="fa-solid fa-warehouse"></i>
                    <span>Nuevo Almacén</span>
                </a>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>