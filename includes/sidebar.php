<?php
// includes/sidebar.php
// No forzar session_start si ya existe (evita warning)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$current = basename($_SERVER['PHP_SELF']);
$baseUrl = '/Sistema_Admin/';

// lista de páginas (como en tu versión original)
$mantenimientoPages = [
    'Mantproductos.php','MantAlmacen.php','MantUbicacion.php',
    'MantDepartamento.php','MantGrupo.php','MantTiposMov.php'
];
$procesoPages = [
    'MantFacturacion.php',
    'MantCotizacion.php'
];
$consultaPages = [
    'Consulta_departamento.php',
    'Consulta_ubicacion.php',
    'Consulta_almacen.php',
    'Consulta_grupo.php',
    'Consulta_tiposmov.php',
    'Consulta_producto.php',
    'Consulta_factura.php',
    'Consulta_usuario.php',
    'Consulta_empresa.php',
    'Consulta_rol.php',
    'Consulta_moneda.php',
    'Consulta_condicionpago.php',
    'Consulta_cliente.php',
    'Consulta_proveedor.php'
];

$isDashboard = ($current === 'dashboard.php');
$isMantenimiento = in_array($current, $mantenimientoPages, true);
$isProceso = in_array($current, $procesoPages, true);
$isConsulta = in_array($current, $consultaPages, true);
$isConfiguracion = ($current === 'Configuracion.php');
$isCxC = ($current === 'CxC.php');
$isCxP = ($current === 'CxP.php');

// permisos desde la sesión
$perms = $_SESSION['permisos'] ?? [];

// helper para generar enlace (si no tiene permiso, lo deja gris y no clickeable)
function nav_link($url, $icon, $label, $allowed, $isActive = false) {
    $active = $isActive ? ' active' : '';
    if ($allowed) {
        return "<a href=\"{$url}\" class=\"submenu-link{$active}\"><i class=\"{$icon}\"></i><span>{$label}</span></a>";
    } else {
        // estilo inline para forzar gris y deshabilitar clic (compatible con tu CSS)
        $style = "opacity:0.45;pointer-events:none;cursor:not-allowed;";
        return "<a href=\"#\" class=\"submenu-link{$active}\" style=\"{$style}\" title=\"Acceso denegado\"><i class=\"{$icon}\"></i><span>{$label}</span></a>";
    }
}
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
            <?php $activeClass = $isDashboard ? 'active' : ''; ?>
            <a href="<?php echo $baseUrl; ?>dashboard.php" class="sidebar-link <?php echo $activeClass; ?>">
                <i class="fa-solid fa-gauge-high"></i>
                <span>Dashboard</span>
            </a>
        </div>

        <!-- Mantenimientos -->
        <div class="sidebar-item">
            <a href="#" class="sidebar-link<?php echo $isMantenimiento ? ' active' : ''; ?>" data-toggle="submenu" data-target="mantenimientosMenu">
                <i class="fa-solid fa-toolbox"></i>
                <span>Mantenimientos</span>
                <i class="fa-solid fa-chevron-down ms-auto chevron <?php echo $isMantenimiento ? 'fa-rotate-180' : ''; ?>"></i>
            </a>
            <div class="submenu <?php echo $isMantenimiento ? 'show' : ''; ?>" id="mantenimientosMenu">
                <?php
                echo nav_link($baseUrl.'Mantproductos.php', 'fa-solid fa-box', 'Productos', ($perms['inv_productos'] ?? 0), $current === 'Mantproductos.php');
                echo nav_link($baseUrl.'MantAlmacen.php', 'fa-solid fa-warehouse', 'Almacenes', ($perms['inv_almacenes'] ?? 0), $current === 'MantAlmacen.php');
                echo nav_link($baseUrl.'MantUbicacion.php', 'fa-solid fa-location-dot', 'Ubicaciones', ($perms['inv_ubicaciones'] ?? 0), $current === 'MantUbicacion.php');
                echo nav_link($baseUrl.'MantDepartamento.php', 'fa-solid fa-building', 'Departamentos', ($perms['inv_departamentos'] ?? 0), $current === 'MantDepartamento.php');
                echo nav_link($baseUrl.'MantGrupo.php', 'fa-solid fa-people-group', 'Grupos', ($perms['inv_grupos'] ?? 0), $current === 'MantGrupo.php');
                // Tipos de movimiento: dependiente de inv_movimientos o inv_cotizaciones (elige lógica que prefieras)
                $allowTipos = ($perms['inv_movimientos'] ?? 0) || ($perms['inv_cotizaciones'] ?? 0);
                echo nav_link($baseUrl.'MantTiposMov.php', 'fa-solid fa-arrows-rotate', 'Tipos de movimiento', $allowTipos, $current === 'MantTiposMov.php');
                ?>
            </div>
        </div>

        <!-- Procesos -->
        <div class="sidebar-item">
            <a href="#" class="sidebar-link<?php echo $isProceso ? ' active' : ''; ?>" data-toggle="submenu" data-target="procesosMenu">
                <i class="fa-solid fa-gears"></i>
                <span>Procesos</span>
                <i class="fa-solid fa-chevron-down ms-auto chevron <?php echo $isProceso ? 'fa-rotate-180' : ''; ?>"></i>
            </a>
            <div class="submenu <?php echo $isProceso ? 'show' : ''; ?>" id="procesosMenu">
                <?php
                // Facturación -> permiso inv_facturacion
                echo nav_link($baseUrl.'Procesos/MantFacturacion.php', 'fa-solid fa-file-invoice-dollar', 'Facturación', ($perms['inv_facturacion'] ?? 0), $current === 'MantFacturacion.php');
                echo nav_link($baseUrl.'Procesos/MantCotizacion.php', 'fa-solid fa-file-invoice-dollar', 'Cotización', ($perms['inv_cotizaciones'] ?? 0), $current === 'MantCotizacion.php');
                echo nav_link($baseUrl.'Procesos/MantCompra.php', 'fa-solid fa-file-invoice-dollar', 'Compra', ($perms['inv_compras'] ?? 0), $current === 'MantCompra.php');
                echo nav_link($baseUrl.'Procesos/MantPago.php', 'fa-solid fa-file-invoice-dollar', 'Pago', ($perms['exp_pagos'] ?? 0), $current === 'MantPago.php');
                ?>
            </div>
        </div>

        <!-- Consultas -->
        <div class="sidebar-item">
            <a href="#" class="sidebar-link<?php echo $isConsulta ? ' active' : ''; ?>" data-toggle="submenu" data-target="consultasMenu">
                <i class="fa-solid fa-magnifying-glass-chart"></i>
                <span>Consultas</span>
                <i class="fa-solid fa-chevron-down ms-auto chevron <?php echo $isConsulta ? 'fa-rotate-180' : ''; ?>"></i>
            </a>
            <div class="submenu <?php echo $isConsulta ? 'show' : ''; ?>" id="consultasMenu">
                <?php
                // Cada consulta requiere inv_consultas (o permisos específicos si quieres)
                $allowConsultas = ($perms['inv_consultas'] ?? 0);
                echo nav_link($baseUrl.'Consultas/Consulta_departamento.php', 'fa-solid fa-building', 'Departamentos', $allowConsultas, $current === 'Consulta_departamento.php');
                echo nav_link($baseUrl.'Consultas/Consulta_ubicacion.php', 'fa-solid fa-location-dot', 'Ubicaciones', $allowConsultas, $current === 'Consulta_ubicacion.php');
                echo nav_link($baseUrl.'Consultas/Consulta_almacen.php', 'fa-solid fa-warehouse', 'Almacenes', $allowConsultas, $current === 'Consulta_almacen.php');
                echo nav_link($baseUrl.'Consultas/Consulta_grupo.php', 'fa-solid fa-people-group', 'Grupos', $allowConsultas, $current === 'Consulta_grupo.php');
                echo nav_link($baseUrl.'Consultas/Consulta_tiposmov.php', 'fa-solid fa-arrows-rotate', 'Tipos de Movimiento', $allowConsultas, $current === 'Consulta_tiposmov.php');
                echo nav_link($baseUrl.'Consultas/Consulta_producto.php', 'fa-solid fa-box', 'Productos', $allowConsultas, $current === 'Consulta_producto.php');
                echo nav_link($baseUrl.'Consultas/Consulta_factura.php', 'fa-solid fa-file-invoice-dollar', 'Facturas', $allowConsultas, $current === 'Consulta_factura.php');
                echo nav_link($baseUrl.'Consultas/Consulta_usuario.php', 'fa-solid fa-user', 'Usuarios', $allowConsultas, $current === 'Consulta_usuario.php');
                echo nav_link($baseUrl.'Consultas/Consulta_empresa.php', 'fa-solid fa-building', 'Empresas', $allowConsultas, $current === 'Consulta_empresa.php');
                echo nav_link($baseUrl.'Consultas/Consulta_moneda.php', 'fa-solid fa-coins', 'Monedas', $allowConsultas, $current === 'Consulta_moneda.php');
                echo nav_link($baseUrl.'Consultas/Consulta_rol.php', 'fa-solid fa-id-badge', 'Roles', $allowConsultas, $current === 'Consulta_rol.php');
                echo nav_link($baseUrl.'Consultas/Consulta_condicionpago.php', 'fa-solid fa-hand-holding-dollar', 'Condiciones Pago', $allowConsultas, $current === 'Consulta_condicionpago.php');
                echo nav_link($baseUrl.'Consultas/Consulta_cliente.php', 'fa-solid fa-user-group', 'Clientes', $allowConsultas, $current === 'Consulta_cliente.php');
                echo nav_link($baseUrl.'Consultas/Consulta_proveedor.php', 'fa-solid fa-truck', 'Proveedores', $allowConsultas, $current === 'Consulta_proveedor.php');
                ?>
            </div>
        </div>

        <div class="sidebar-item">
            <?php
            // Reportes -> inv_reportes permiso
            $allowReportes = ($perms['inv_reportes'] ?? 0);
            if ($allowReportes) {
                echo '<a href="#" class="sidebar-link"><i class="fa-solid fa-chart-pie"></i><span>Reportes</span></a>';
            } else {
                echo '<a href="#" class="sidebar-link" style="opacity:0.45;pointer-events:none;cursor:not-allowed;" title="Acceso denegado"><i class="fa-solid fa-chart-pie"></i><span>Reportes</span></a>';
            }
            ?>
        </div>

        <div class="sidebar-item">
            <?php
            // Configuración (mostrar si tiene alguna conf_* permiso)
            $allowConf = ($perms['conf_usuario'] ?? 0) || ($perms['conf_roles'] ?? 0) || ($perms['conf_empresa'] ?? 0) || ($perms['conf_moneda'] ?? 0) || ($perms['conf_condicion'] ?? 0);
            if ($allowConf) {
                echo '<a href="'.$baseUrl.'Configuracion.php" class="sidebar-link '.($isConfiguracion ? 'active':'').'"><i class="fa-solid fa-sliders"></i><span>Configuración</span></a>';
            } else {
                echo '<a href="#" class="sidebar-link" style="opacity:0.45;pointer-events:none;cursor:not-allowed;" title="Acceso denegado"><i class="fa-solid fa-sliders"></i><span>Configuración</span></a>';
            }
            ?>
        </div>

        <div class="sidebar-item">
            <?php
            // Configuración (mostrar si tiene alguna conf_* permiso)
            $allowConf = ($perms['conf_usuario'] ?? 0) || ($perms['conf_roles'] ?? 0) || ($perms['conf_empresa'] ?? 0) || ($perms['conf_moneda'] ?? 0) || ($perms['conf_condicion'] ?? 0);
            if ($allowConf) {
                echo '<a href="'.$baseUrl.'CxC.php" class="sidebar-link '.($isCxC ? 'active':'').'"><i class="fa-solid fa-hand-holding-dollar"></i><span>Cuentas por Cobrar</span></a>';
            } else {
                echo '<a href="#" class="sidebar-link" style="opacity:0.45;pointer-events:none;cursor:not-allowed;" title="Acceso denegado"><i class="fa-solid fa-hand-holding-dollar"></i><span>Cuentas por Cobrar</span></a>';
            }
            ?>
        </div>

        <div class="sidebar-item">
            <?php
            // Configuración (mostrar si tiene alguna conf_* permiso)
            $allowConf = ($perms['conf_usuario'] ?? 0) || ($perms['conf_roles'] ?? 0) || ($perms['conf_empresa'] ?? 0) || ($perms['conf_moneda'] ?? 0) || ($perms['conf_condicion'] ?? 0);
            if ($allowConf) {
                echo '<a href="'.$baseUrl.'CxP.php" class="sidebar-link '.($isCxP ? 'active':'').'"><i class="fa-solid fa-money-check-dollar"></i><span>Cuentas por Pagar</span></a>';
            } else {
                echo '<a href="#" class="sidebar-link" style="opacity:0.45;pointer-events:none;cursor:not-allowed;" title="Acceso denegado"><i class="fa-solid fa-money-check-dollar"></i><span>Cuentas por Pagar</span></a>';
            }
            ?>
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
