<?php
    $current = basename($_SERVER['PHP_SELF']);
    $baseUrl = '/Sistema_Admin/';
    $mantenimientoPages = [
        'Mantproductos.php','MantAlmacen.php','MantUbicacion.php',
        'MantDepartamento.php','MantGrupo.php','MantTiposMov.php'
    ];
    $procesoPages = [
        'MantFacturacion.php','Factura_listar.php'
    ];
    $consultaPages = [
        'Consulta_departamento.php',
        'Consulta_ubicacion.php',
        'Consulta_almacen.php',
        'Consulta_grupo.php',
        'Consulta_tiposmov.php',
        'Consulta_producto.php',
        'Consulta_factura.php'
    ];
    $isDashboard = ($current === 'dashboard.php');
    $isMantenimiento = in_array($current, $mantenimientoPages, true);
    $isProceso = in_array($current, $procesoPages, true);
    $isConsulta = in_array($current, $consultaPages, true);
?>

<div class="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo">
            <i class="fa-solid fa-shield-halved"></i>
            <span>AdminPanel</span>
        </div>
    </div>

    <div class="sidebar-menu">
        <div class="sidebar-item">
            <a href="<?php echo $baseUrl; ?>dashboard.php" class="sidebar-link <?php echo $isDashboard ? 'active' : ''; ?>">
                <i class="fa-solid fa-gauge-high"></i>
                <span>Dashboard</span>
            </a>
        </div>

        <div class="sidebar-item">
            <a href="#" class="sidebar-link<?php echo $isMantenimiento ? ' active' : ''; ?>" data-toggle="submenu" data-target="mantenimientosMenu">
                <i class="fa-solid fa-toolbox"></i>
                <span>Mantenimientos</span>
                <i class="fa-solid fa-chevron-down ms-auto chevron <?php echo $isMantenimiento ? 'fa-rotate-180' : ''; ?>"></i>
            </a>
            <div class="submenu <?php echo $isMantenimiento ? 'show' : ''; ?>" id="mantenimientosMenu">
                <a href="<?php echo $baseUrl; ?>Mantproductos.php" class="submenu-link <?php echo $current === 'Mantproductos.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-box"></i>
                    <span>Productos</span>
                </a>
                <a href="<?php echo $baseUrl; ?>MantAlmacen.php" class="submenu-link <?php echo $current === 'MantAlmacen.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-warehouse"></i>
                    <span>Almacenes</span>
                </a>
                <a href="<?php echo $baseUrl; ?>MantUbicacion.php" class="submenu-link <?php echo $current === 'MantUbicacion.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-location-dot"></i>
                    <span>Ubicaciones</span>
                </a>
                <a href="<?php echo $baseUrl; ?>MantDepartamento.php" class="submenu-link <?php echo $current === 'MantDepartamento.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-building"></i>
                    <span>Departamentos</span>
                </a>
                <a href="<?php echo $baseUrl; ?>MantGrupo.php" class="submenu-link <?php echo $current === 'MantGrupo.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-people-group"></i>
                    <span>Grupos</span>
                </a>
                <a href="<?php echo $baseUrl; ?>MantTiposMov.php" class="submenu-link <?php echo $current === 'MantTiposMov.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-arrows-rotate"></i>
                    <span>Tipos de movimiento</span>
                </a>
            </div>
        </div>

        <div class="sidebar-item">
            <a href="#" class="sidebar-link<?php echo $isProceso ? ' active' : ''; ?>" data-toggle="submenu" data-target="procesosMenu">
                <i class="fa-solid fa-gears"></i>
                <span>Procesos</span>
                <i class="fa-solid fa-chevron-down ms-auto chevron <?php echo $isProceso ? 'fa-rotate-180' : ''; ?>"></i>
            </a>
            <div class="submenu <?php echo $isProceso ? 'show' : ''; ?>" id="procesosMenu">
                <a href="<?php echo $baseUrl; ?>Procesos/MantFacturacion.php" class="submenu-link <?php echo $current === 'MantFacturacion.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-file-invoice-dollar"></i>
                    <span>Facturación</span>
                </a>
                <a href="<?php echo $baseUrl; ?>Procesos/Factura_listar.php" class="submenu-link <?php echo $current === 'Factura_listar.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-list"></i>
                    <span>Listado de Facturas</span>
                </a>
            </div>
        </div>

        <div class="sidebar-item">
            <a href="#" class="sidebar-link<?php echo $isConsulta ? ' active' : ''; ?>" data-toggle="submenu" data-target="consultasMenu">
                <i class="fa-solid fa-magnifying-glass-chart"></i>
                <span>Consultas</span>
                <i class="fa-solid fa-chevron-down ms-auto chevron <?php echo $isConsulta ? 'fa-rotate-180' : ''; ?>"></i>
            </a>
            <div class="submenu <?php echo $isConsulta ? 'show' : ''; ?>" id="consultasMenu">
                <a href="<?php echo $baseUrl; ?>Consultas/Consulta_departamento.php" class="submenu-link <?php echo $current === 'Consulta_departamento.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-building"></i>
                    <span>Departamentos</span>
                </a>
                <a href="<?php echo $baseUrl; ?>Consultas/Consulta_ubicacion.php" class="submenu-link <?php echo $current === 'Consulta_ubicacion.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-location-dot"></i>
                    <span>Ubicaciones</span>
                </a>
                <a href="<?php echo $baseUrl; ?>Consultas/Consulta_almacen.php" class="submenu-link <?php echo $current === 'Consulta_almacen.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-warehouse"></i>
                    <span>Almacenes</span>
                </a>
                <a href="<?php echo $baseUrl; ?>Consultas/Consulta_grupo.php" class="submenu-link <?php echo $current === 'Consulta_grupo.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-people-group"></i>
                    <span>Grupos</span>
                </a>
                <a href="<?php echo $baseUrl; ?>Consultas/Consulta_tiposmov.php" class="submenu-link <?php echo $current === 'Consulta_tiposmov.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-arrows-rotate"></i>
                    <span>Tipos de Movimiento</span>
                </a>
                <a href="<?php echo $baseUrl; ?>Consultas/Consulta_producto.php" class="submenu-link <?php echo $current === 'Consulta_producto.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-box"></i>
                    <span>Productos</span>
                </a>
                <a href="<?php echo $baseUrl; ?>Consultas/Consulta_factura.php" class="submenu-link <?php echo $current === 'Consulta_factura.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-file-invoice-dollar"></i>
                    <span>Facturas</span>
                </a>
                <!-- Futuras consultas
                <a href="#" class="submenu-link">
                    <i class="fa-solid fa-cart-shopping"></i>
                    <span>Compras</span>
                </a>
                <a href="#" class="submenu-link">
                    <i class="fa-solid fa-right-left"></i>
                    <span>Entradas / Salidas</span>
                </a>
                -->
            </div>
        </div>

        <div class="sidebar-item">
            <a href="#" class="sidebar-link">
                <i class="fa-solid fa-chart-pie"></i>
                <span>Reportes</span>
            </a>
        </div>

        <div class="sidebar-item mt-4">
            <a href="<?php echo $baseUrl; ?>logout.php" class="sidebar-link text-danger">
                <i class="fa-solid fa-arrow-right-from-bracket"></i>
                <span>Cerrar sesión</span>
            </a>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle genérico para todos los submenús
    document.querySelectorAll('[data-toggle="submenu"]').forEach(function(btn){
        const targetId = btn.getAttribute('data-target');
        const menu = document.getElementById(targetId);
        const arrow = btn.querySelector('.chevron');
        if(!menu) return;

        btn.addEventListener('click', function(e){
            e.preventDefault();
            menu.classList.toggle('show');
            if(arrow){ arrow.classList.toggle('fa-rotate-180'); }
        });
    });

    // Cerrar al hacer click fuera
    document.addEventListener('click', function(e){
        document.querySelectorAll('[data-toggle="submenu"]').forEach(function(btn){
            const targetId = btn.getAttribute('data-target');
            const menu = document.getElementById(targetId);
            const arrow = btn.querySelector('.chevron');
            if(!menu) return;
            const clickedInside = btn.contains(e.target) || menu.contains(e.target);
            if(!clickedInside){
                menu.classList.remove('show');
                if(arrow){ arrow.classList.remove('fa-rotate-180'); }
            }
        });
    });
});
</script>