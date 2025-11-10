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
        $total_productos = $row['total'];
    }
    
    $almacenes_query = "SELECT COUNT(*) as total FROM almacen";
    $result = $conexion->query($almacenes_query);
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $total_almacenes = $row['total'];
    }
    
    $movimientos_query = "SELECT COUNT(*) as total FROM movimiento_inventario WHERE DATE(fecha) = CURDATE()";
    $result = $conexion->query($movimientos_query);
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $total_movimientos = $row['total'];
    }
    
    $usuarios_query = "SELECT COUNT(*) as total FROM usuario";
    $result = $conexion->query($usuarios_query);
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $total_usuarios = $row['total'];
    }
    
} else {
    error_log("Conexión a BD no disponible en dashboard");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Panel Administrativo</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
    :root {
        --primary: #004aad;
        --primary-dark: #062c6d;
        --secondary: #6c757d;
        --success: #28a745;
        --info: #17a2b8;
        --warning: #ffc107;
        --danger: #dc3545;
        --light: #f8f9fa;
        --dark: #343a40;
        --sidebar-width: 260px;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        background: #f5f7fb;
        font-family: "Poppins", sans-serif;
        color: #333;
        min-height: 100vh;
    }

    /* Sidebar */
    .sidebar {
        width: var(--sidebar-width);
        height: 100vh;
        background: linear-gradient(180deg, var(--primary-dark), var(--primary));
        color: white;
        position: fixed;
        left: 0;
        top: 0;
        padding-top: 30px;
        box-shadow: 3px 0 15px rgba(0,0,0,0.2);
        z-index: 100;
        overflow-y: auto;
        scrollbar-width: thin;
        scrollbar-color: rgba(255,255,255,0.3) transparent;
    }

    .sidebar::-webkit-scrollbar {
        width: 4px;
    }
    
    .sidebar::-webkit-scrollbar-track {
        background: transparent;
    }
    
    .sidebar::-webkit-scrollbar-thumb {
        background-color: rgba(255,255,255,0.3);
        border-radius: 2px;
    }

    .sidebar-header {
        text-align: center;
        margin-bottom: 30px;
        padding: 0 20px;
    }

    .sidebar-logo {
        font-weight: 700;
        font-size: 1.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }

    .sidebar-menu {
        padding: 0 15px;
    }

    .sidebar-item {
        margin-bottom: 5px;
    }

    .sidebar-link {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px 20px;
        color: white;
        text-decoration: none;
        transition: all 0.25s;
        border-left: 3px solid transparent;
        border-radius: 8px;
        margin-bottom: 5px;
    }

    .sidebar-link:hover, .sidebar-link.active {
        background: rgba(255,255,255,0.15);
        border-left-color: #fff;
    }

    .sidebar-link i {
        width: 20px;
        text-align: center;
        font-size: 1.1rem;
    }

    .submenu {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s ease;
        background: rgba(0,0,0,0.1);
        border-radius: 8px;
        margin: 5px 0;
    }

    .submenu.show {
        max-height: 500px;
    }

    .submenu-link {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 20px 12px 50px;
        color: #dcdcdc;
        text-decoration: none;
        font-size: 0.9rem;
        transition: all 0.2s;
        border-left: 3px solid transparent;
    }

    .submenu-link:hover, .submenu-link.active {
        background: rgba(255,255,255,0.1);
        color: #fff;
        border-left-color: rgba(255,255,255,0.5);
    }

    
    .main-content {
        margin-left: var(--sidebar-width);
        min-height: 100vh;
        background: #f5f7fb;
    }

    .top-header {
        background: white;
        padding: 20px 40px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .page-title h1 {
        font-size: 1.8rem;
        font-weight: 600;
        margin: 0;
        color: var(--primary);
    }

    .user-info {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .user-avatar {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        background: var(--primary);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 1.1rem;
    }

    .user-details .username {
        font-weight: 600;
        font-size: 1rem;
    }

    .user-details .role {
        color: var(--secondary);
        font-size: 0.85rem;
    }

    .content-area {
        padding: 40px;
        animation: fadeIn 0.7s;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }


    .welcome-box {
        background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        color: white;
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0,74,173,0.2);
        display: flex;
        align-items: center;
        gap: 25px;
        margin-bottom: 40px;
    }

    .welcome-icon {
        font-size: 48px;
        opacity: 0.9;
    }

    .welcome-text h3 {
        margin: 0;
        font-weight: 600;
        font-size: 1.8rem;
    }

    .welcome-text p {
        margin: 8px 0 0;
        opacity: 0.9;
        font-size: 1.1rem;
    }

   
    .card-dashboard {
        padding: 30px 25px;
        background: white;
        border-radius: 12px;
        text-align: center;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        transition: all 0.3s;
        height: 100%;
        border: none;
        position: relative;
        overflow: hidden;
    }

    .card-dashboard::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 5px;
        background: linear-gradient(90deg, var(--primary), var(--primary-dark));
    }

    .card-dashboard:hover {
        transform: translateY(-8px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.15);
    }

    .card-dashboard i {
        font-size: 46px;
        margin-bottom: 20px;
        color: var(--primary);
        transition: transform 0.3s;
    }

    .card-dashboard:hover i {
        transform: scale(1.1);
    }

    .card-dashboard h5 {
        font-weight: 600;
        margin-bottom: 12px;
        font-size: 1.3rem;
        color: var(--dark);
    }

    .card-dashboard p {
        color: var(--secondary);
        font-size: 1rem;
        margin: 0;
        line-height: 1.5;
    }

    .stats-section {
        margin-top: 50px;
    }

    .section-title {
        font-weight: 600;
        margin-bottom: 25px;
        color: var(--primary);
        position: relative;
        padding-bottom: 12px;
        font-size: 1.5rem;
    }

    .section-title::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 60px;
        height: 4px;
        background: var(--primary);
        border-radius: 2px;
    }

    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        display: flex;
        align-items: center;
        gap: 20px;
        transition: all 0.3s;
        height: 100%;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.12);
    }

    .stat-icon {
        width: 70px;
        height: 70px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        color: white;
        flex-shrink: 0;
    }

    .stat-icon.blue {
        background: linear-gradient(135deg, #4dabf7, #228be6);
    }

    .stat-icon.green {
        background: linear-gradient(135deg, #69db7c, #40c057);
    }

    .stat-icon.orange {
        background: linear-gradient(135deg, #ffa94d, #ff922b);
    }

    .stat-icon.purple {
        background: linear-gradient(135deg, #cc5de8, #be4bdb);
    }

    .stat-info h4 {
        margin: 0;
        font-weight: 700;
        font-size: 2rem;
        color: var(--dark);
    }

    .stat-info p {
        margin: 5px 0 0;
        color: var(--secondary);
        font-size: 1rem;
    }

    .quick-actions {
        margin-top: 50px;
    }

    .action-buttons {
        display: flex;
        gap: 20px;
        margin-top: 20px;
    }

    .action-btn {
        background: white;
        border: 2px solid #e9ecef;
        border-radius: 10px;
        padding: 25px;
        text-align: center;
        transition: all 0.3s;
        cursor: pointer;
        text-decoration: none;
        color: inherit;
        flex: 1;
        max-width: 300px;
    }

    .action-btn:hover {
        border-color: var(--primary);
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        color: inherit;
        text-decoration: none;
    }

    .action-btn i {
        font-size: 36px;
        color: var(--primary);
        margin-bottom: 12px;
    }

    .action-btn span {
        display: block;
        font-weight: 500;
        color: var(--dark);
        font-size: 1.1rem;
    }
    #mantenimientosMenu:not(.show) {
        max-height: 0 !important;
        overflow: hidden !important;
    }
    
    #mantenimientosMenu.show {
        max-height: 300px !important;
        overflow-y: auto !important;
        margin-right: 4px !important;
    }

    #mantenimientosMenu::-webkit-scrollbar {
        width: 4px;
    }
    
    #mantenimientosMenu::-webkit-scrollbar-track {
        background: transparent;
    }
    
    #mantenimientosMenu::-webkit-scrollbar-thumb {
        background-color: rgba(255,255,255,0.3);
        border-radius: 2px;
    }
    
    .fa-chevron-down {
        transition: transform 0.3s ease !important;
    }
    
    .fa-chevron-down.fa-rotate-180 {
        transform: rotate(180deg) !important;
    }
</style>
</head>

<body>

<div class="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo">
            <i class="fa-solid fa-shield-halved"></i>
            <span>AdminPanel</span>
        </div>
    </div>

    <div class="sidebar-menu">
        <div class="sidebar-item">
            <a href="#" class="sidebar-link active">
                <i class="fa-solid fa-gauge-high"></i>
                <span>Dashboard</span>
            </a>
        </div>

        <div class="sidebar-item">
            <a href="#" class="sidebar-link" id="mantenimientosBtn">
                <i class="fa-solid fa-toolbox"></i>
                <span>Mantenimientos</span>
                <i class="fa-solid fa-chevron-down ms-auto" id="mantenimientosArrow"></i>
            </a>
    
            <div class="submenu" id="mantenimientosMenu">
                <a href="Mantproductos.php" class="submenu-link">
                    <i class="fa-solid fa-box"></i>
                    <span>Productos</span>
                </a>
                <a href="MantAlmacen.php" class="submenu-link">
                    <i class="fa-solid fa-warehouse"></i>
                    <span>Almacenes</span>
                </a>
                <a href="MantUbicacion.php" class="submenu-link">
                    <i class="fa-solid fa-location-dot"></i>
                    <span>Ubicaciones</span>
                </a>
                <a href="MantDepartamento.php" class="submenu-link">
                    <i class="fa-solid fa-building"></i>
                    <span>Departamentos</span>
                </a>
                <a href="MantGrupo.php" class="submenu-link">
                    <i class="fa-solid fa-people-group"></i>
                    <span>Grupos</span>
                </a>
                <a href="MantTiposMov.php" class="submenu-link">
                    <i class="fa-solid fa-arrows-rotate"></i>
                    <span>Tipos de movimiento</span>
                </a>
            </div>
        </div>

        <div class="sidebar-item">
            <a href="#" class="sidebar-link">
                <i class="fa-solid fa-gears"></i>
                <span>Procesos</span>
            </a>
        </div>

        <div class="sidebar-item">
            <a href="#" class="sidebar-link" id="consultasBtn">
                <i class="fa-solid fa-magnifying-glass-chart"></i>
                <span>Consultas</span>
                <i class="fa-solid fa-chevron-down ms-auto" id="consultasArrow"></i>
            </a>

            <div class="submenu" id="consultasMenu">
                <a href="#" class="submenu-link">
                    <i class="fa-solid fa-file-signature"></i>
                    <span>Cotizaciones</span>
                </a>
                <a href="#" class="submenu-link">
                    <i class="fa-solid fa-cart-shopping"></i>
                    <span>Compras</span>
                </a>
                <a href="#" class="submenu-link">
                    <i class="fa-solid fa-right-left"></i>
                    <span>Entradas / Salidas</span>
                </a>
                <a href="#" class="submenu-link">
                    <i class="fa-solid fa-rotate-left"></i>
                    <span>Devoluciones</span>
                </a>
                <a href="#" class="submenu-link">
                    <i class="fa-solid fa-file-invoice-dollar"></i>
                    <span>Facturas</span>
                </a>
            </div>
        </div>

        <div class="sidebar-item">
            <a href="#" class="sidebar-link">
                <i class="fa-solid fa-chart-pie"></i>
                <span>Reportes</span>
            </a>
        </div>

        <div class="sidebar-item mt-4">
            <a href="logout.php" class="sidebar-link text-danger">
                <i class="fa-solid fa-arrow-right-from-bracket"></i>
                <span>Cerrar sesión</span>
            </a>
        </div>
    </div>
</div>


<div class="main-content">
    <div class="top-header">
        <div class="page-title">
            <h1>Panel de Control</h1>
        </div>
        <div class="user-info">
            <div class="user-avatar">
                <?php echo strtoupper(substr($usuario, 0, 1)); ?>
            </div>
            <div class="user-details">
                <div class="username"><?php echo $usuario; ?></div>
                <div class="role">Administrador</div>
            </div>
        </div>
    </div>

    <div class="content-area">
        <div class="welcome-box">
            <div class="welcome-icon">
                <i class="fa-solid fa-user-tie"></i>
            </div>
            <div class="welcome-text">
                <h3>¡Bienvenido <?php echo $usuario; ?>!</h3>
                <p>Panel administrativo del sistema - <?php echo date('d/m/Y'); ?></p>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-md-3">
                <div class="card-dashboard">
                    <i class="fa-solid fa-toolbox"></i>
                    <h5>Mantenimientos</h5>
                    <p>Gestionar catálogos y configuraciones del sistema</p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card-dashboard">
                    <i class="fa-solid fa-gears"></i>
                    <h5>Procesos</h5>
                    <p>Operaciones y transacciones de negocio</p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card-dashboard">
                    <i class="fa-solid fa-search"></i>
                    <h5>Consultas</h5>
                    <p>Buscar y consultar información del sistema</p>
                </div>
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
                        <div class="stat-icon blue">
                            <i class="fa-solid fa-box"></i>
                        </div>
                        <div class="stat-info">
                            <h4><?php echo $total_productos; ?></h4>
                            <p>Productos Registrados</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon green">
                            <i class="fa-solid fa-warehouse"></i>
                        </div>
                        <div class="stat-info">
                            <h4><?php echo $total_almacenes; ?></h4>
                            <p>Almacenes Activos</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon orange">
                            <i class="fa-solid fa-arrows-rotate"></i>
                        </div>
                        <div class="stat-info">
                            <h4><?php echo $total_movimientos; ?></h4>
                            <p>Movimientos Hoy</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon purple">
                            <i class="fa-solid fa-users"></i>
                        </div>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const mantenimientosBtn = document.getElementById('mantenimientosBtn');
    const mantenimientosMenu = document.getElementById('mantenimientosMenu');
    const mantenimientosArrow = document.getElementById('mantenimientosArrow');

    mantenimientosBtn.addEventListener('click', function(e) {
        e.preventDefault();
        mantenimientosMenu.classList.toggle('show');
        mantenimientosArrow.classList.toggle('fa-rotate-180');
    });

    const consultasBtn = document.getElementById('consultasBtn');
    const consultasMenu = document.getElementById('consultasMenu');
    const consultasArrow = document.getElementById('consultasArrow');

    consultasBtn.addEventListener('click', function(e) {
        e.preventDefault();
        consultasMenu.classList.toggle('show');
        consultasArrow.classList.toggle('fa-rotate-180');
    });

    document.addEventListener('click', function(e) {
        if (!mantenimientosBtn.contains(e.target) && !mantenimientosMenu.contains(e.target)) {
            mantenimientosMenu.classList.remove('show');
            mantenimientosArrow.classList.remove('fa-rotate-180');
        }
        if (!consultasBtn.contains(e.target) && !consultasMenu.contains(e.target)) {
            consultasMenu.classList.remove('show');
            consultasArrow.classList.remove('fa-rotate-180');
        }
    });
});

</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const menu = document.getElementById('mantenimientosMenu');
    const arrow = document.getElementById('mantenimientosArrow');

    if(menu) {
        menu.classList.remove('show');
        menu.style.maxHeight = '0';
    }
    if(arrow) {
        arrow.classList.remove('fa-rotate-180');
    }
    
});
</script>
</body>
</html>