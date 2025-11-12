<?php
session_start();
if(!isset($_SESSION['usuario'])) {
    header("Location: ../index.html");
    exit();
}
require_once '../conexion.php';
include '../includes/header.php';
include '../includes/sidebar.php';
?>
<div class="main-content">
    <div class="main-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col">
                    <h4 class="page-title">
                        <i class="fa-solid fa-magnifying-glass me-2"></i>Consulta de Facturas
                    </h4>
                    <p class="page-subtitle">Visualiza las facturas de venta registradas en el sistema</p>
                </div>
                <div class="col-auto">
                    <div class="user-info">
                        <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['usuario'], 0, 1)); ?></div>
                        <div class="user-details">
                            <div class="username"><?php echo $_SESSION['usuario']; ?></div>
                            <div class="role">Administrador</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="content-area">
        <div class="container-fluid">
            <!-- Filtros de búsqueda -->
            <div class="card card-custom fade-in mb-4">
                <div class="card-header card-header-custom d-flex align-items-center justify-content-between">
                    <h5 class="mb-0"><i class="fa-solid fa-magnifying-glass me-2"></i>Buscar Facturas</h5>
                    <span class="text-muted small" id="resultCount">
                        <?php
                        if (isset($conexion) && $conexion instanceof mysqli) {
                            $countQuery = "SELECT COUNT(*) as total FROM factura";
                            $countResult = $conexion->query($countQuery);
                            if ($countResult && $countResult->num_rows > 0) {
                                $countRow = $countResult->fetch_assoc();
                                echo $countRow['total'] . " facturas encontradas";
                            }
                        }
                        ?>
                    </span>
                </div>
                <div class="card-body">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label for="searchInput" class="form-label text-required">N° Factura, Cliente o Usuario</label>
                            <input type="text" class="form-control form-control-custom" id="searchInput" placeholder="Ej: FAC-00000001, Cliente...">
                        </div>
                        <div class="col-md-3">
                            <label for="filtroFechaDesde" class="form-label">Fecha Desde</label>
                            <input type="date" class="form-control form-control-custom" id="filtroFechaDesde">
                        </div>
                        <div class="col-md-3">
                            <label for="filtroFechaHasta" class="form-label">Fecha Hasta</label>
                            <input type="date" class="form-control form-control-custom" id="filtroFechaHasta">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla de resultados -->
            <div class="card card-custom fade-in">
                <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fa-solid fa-list me-2"></i>Lista de Facturas</h5>
                    <div class="input-group input-group-sm" style="max-width:200px">
                        <span class="input-group-text bg-primary text-white"><i class="fa-solid fa-filter"></i></span>
                        <select id="filtroEstado" class="form-select">
                            <option value="">Todos</option>
                            <option value="1">Activos</option>
                            <option value="0">Anulados</option>
                        </select>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-mantenimiento" id="facturaTable">
                            <thead>
                                <tr>
                                    <th>N° Factura</th>
                                    <th>Cliente</th>
                                    <th>Fecha</th>
                                    <th>Condición</th>
                                    <th>Total</th>
                                    <th>Usuario</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if (isset($conexion) && $conexion instanceof mysqli) {
                                    $query = "SELECT f.*, c.nombre as cliente_nombre, cp.nombre as condicion_nombre, 
                                              u.nombre as usuario_nombre
                                              FROM factura f
                                              INNER JOIN cliente c ON f.cliente_id = c.id_clientes
                                              INNER JOIN condicion_pago cp ON f.condicion_id = cp.id_condiciones_pago
                                              INNER JOIN usuario u ON f.usuario_id = u.id_usuarios
                                              ORDER BY f.fecha DESC, f.id_facturas DESC";
                                    $result = $conexion->query($query);
                                    
                                    if ($result && $result->num_rows > 0) {
                                        while ($row = $result->fetch_assoc()) {
                                            $estadoBadge = $row['activo'] == 1
                                                ? "<span style='background-color:#d1e7dd;color:#0f5132;padding:6px 12px;border-radius:20px;font-weight:500;'>
                                                     <i class='fas fa-circle me-1' style='font-size:8px;color:#198754;'></i>Activo
                                                   </span>"
                                                : "<span style='background-color:#f8d7da;color:#842029;padding:6px 12px;border-radius:20px;font-weight:500;'>
                                                     <i class='fas fa-circle me-1' style='font-size:8px;color:#dc3545;'></i>Anulado
                                                   </span>";
                                            
                                            echo "<tr data-activo='{$row['activo']}' data-fecha='{$row['fecha']}'>";
                                            echo "<td><strong>" . htmlspecialchars($row['numero_documento']) . "</strong></td>";
                                            echo "<td>" . htmlspecialchars($row['cliente_nombre']) . "</td>";
                                            echo "<td>" . date('d/m/Y', strtotime($row['fecha'])) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['condicion_nombre']) . "</td>";
                                            echo "<td><strong>$" . number_format($row['total'], 2) . "</strong></td>";
                                            echo "<td>" . htmlspecialchars($row['usuario_nombre']) . "</td>";
                                            echo "<td>{$estadoBadge}</td>";
                                            echo "</tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='7' class='text-center py-4'>
                                                <i class='fa-solid fa-inbox fa-2x text-muted mb-3'></i><br>
                                                <span class='text-muted'>No se encontraron facturas registradas</span>
                                              </td></tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='7' class='text-center py-4 text-danger'>
                                            <i class='fa-solid fa-triangle-exclamation fa-2x mb-3'></i><br>
                                            <span>Error de conexión a la base de datos</span>
                                          </td></tr>";
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const resultCount = document.getElementById('resultCount');
    const filtroEstado = document.getElementById('filtroEstado');
    const filtroFechaDesde = document.getElementById('filtroFechaDesde');
    const filtroFechaHasta = document.getElementById('filtroFechaHasta');

    function aplicarFiltros() {
        const texto = searchInput.value.toLowerCase();
        const estado = filtroEstado.value;
        const fechaDesde = filtroFechaDesde.value;
        const fechaHasta = filtroFechaHasta.value;
        const rows = document.querySelectorAll('#facturaTable tbody tr');
        let visibles = 0;

        rows.forEach(row => {
            if (row.cells.length < 7) return;
            
            const numeroFactura = row.cells[0].textContent.toLowerCase();
            const cliente = row.cells[1].textContent.toLowerCase();
            const usuario = row.cells[5].textContent.toLowerCase();
            const activo = row.getAttribute('data-activo');
            const fecha = row.getAttribute('data-fecha');

            let show = numeroFactura.includes(texto) || cliente.includes(texto) || usuario.includes(texto);
            
            if (estado !== '' && activo !== estado) {
                show = false;
            }

            if (fechaDesde && fecha < fechaDesde) {
                show = false;
            }

            if (fechaHasta && fecha > fechaHasta) {
                show = false;
            }

            row.style.display = show ? '' : 'none';
            if (show) visibles++;
        });

        if (texto === '' && estado === '' && !fechaDesde && !fechaHasta) {
            resultCount.textContent = rows.length + ' facturas encontradas';
        } else {
            resultCount.textContent = visibles + ' facturas encontradas (filtradas)';
        }
    }

    searchInput.addEventListener('keyup', aplicarFiltros);
    filtroEstado.addEventListener('change', aplicarFiltros);
    filtroFechaDesde.addEventListener('change', aplicarFiltros);
    filtroFechaHasta.addEventListener('change', aplicarFiltros);
    searchInput.focus();
});
</script>
<?php include '../includes/footer.php'; ?>
