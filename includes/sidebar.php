<div class="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo">
            <i class="fa-solid fa-shield-halved"></i>
            <span>AdminPanel</span>
        </div>
    </div>

    <div class="sidebar-menu">
        <div class="sidebar-item">
            <a href="dashboard.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
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
            <div class="submenu show" id="mantenimientosMenu">
                <a href="Mantproductos.php" class="submenu-link <?php echo basename($_SERVER['PHP_SELF']) == 'Mantproductos.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-box"></i>
                    <span>Productos</span>
                </a>
                <a href="MantAlmacen.php" class="submenu-link <?php echo basename($_SERVER['PHP_SELF']) == 'MantAlmacen.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-warehouse"></i>
                    <span>Almacenes</span>
                </a>
                <a href="MantUbicacion.php" class="submenu-link <?php echo basename($_SERVER['PHP_SELF']) == 'MantUbicacion.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-location-dot"></i>
                    <span>Ubicaciones</span>
                </a>
                <a href="MantDepartamento.php" class="submenu-link <?php echo basename($_SERVER['PHP_SELF']) == 'MantDepartamento.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-building"></i>
                    <span>Departamentos</span>
                </a>
                <a href="MantGrupo.php" class="submenu-link <?php echo basename($_SERVER['PHP_SELF']) == 'MantGrupo.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-people-group"></i>
                    <span>Grupos</span>
                </a>
                <a href="MantTiposMov.php" class="submenu-link <?php echo basename($_SERVER['PHP_SELF']) == 'MantTiposMov.php' ? 'active' : ''; ?>">
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
            <a href="#" class="sidebar-link">
                <i class="fa-solid fa-search"></i>
                <span>Consultas</span>
            </a>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const mantenimientosBtn = document.getElementById('mantenimientosBtn');
    const mantenimientosMenu = document.getElementById('mantenimientosMenu');
    const mantenimientosArrow = document.getElementById('mantenimientosArrow');

    // Siempre expandido
    mantenimientosMenu.classList.add('show');
    mantenimientosArrow.classList.add('fa-rotate-180');

    mantenimientosBtn.addEventListener('click', function(e) {
        e.preventDefault();
        mantenimientosMenu.classList.toggle('show');
        mantenimientosArrow.classList.toggle('fa-rotate-180');
    });
});
</script>