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
                        <i class="fa-solid fa-magnifying-glass me-2"></i>Consulta de Condiciones de Pago
                    </h4>
                    <p class="page-subtitle">Visualiza las condiciones de pago y su plazo</p>
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
            <div class="card card-custom fade-in mb-4">
                <div class="card-header card-header-custom d-flex align-items-center justify-content-between">
                    <h5 class="mb-0"><i class="fa-solid fa-magnifying-glass me-2"></i>Buscar Condiciones de Pago</h5>
                    <span class="text-muted small" id="resultCount">
                        <?php
                        if (isset($conexion) && $conexion instanceof mysqli) {
                            $countQuery = "SELECT COUNT(*) as total FROM condicion_pago";
                            $countResult = $conexion->query($countQuery);
                            if ($countResult && $countResult->num_rows > 0) {
                                $countRow = $countResult->fetch_assoc();
                                echo $countRow['total'] . " condiciones encontradas";
                            }
                        }
                        ?>
                    </span>
                </div>
                <div class="card-body">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-5">
                            <label for="searchInput" class="form-label text-required">Nombre</label>
                            <input type="text" class="form-control form-control-custom" id="searchInput" placeholder="Ej: Contado, 30 días, Crédito..."></input>
                        </div>
                        <div class="col-md-3">
                            <label for="filtroEstado" class="form-label">Estado</label>
                            <select id="filtroEstado" class="form-select form-control-custom">
                                <option value="">Todos</option>
                                <option value="1">Activas</option>
                                <option value="0">Inactivas</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="filtroPlazo" class="form-label">Plazo (máx. días)</label>
                            <div class="input-group">
                                <input type="number" min="0" class="form-control form-control-custom" id="filtroPlazo" placeholder="Ej: 30">
                                <span class="input-group-text"><i class="fa-regular fa-clock"></i></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card card-custom fade-in">
                <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fa-solid fa-list me-2"></i>Lista de Condiciones</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-mantenimiento" id="condicionesTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Días Plazo</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if (isset($conexion) && $conexion instanceof mysqli) {
                                    $query = "SELECT id_condiciones_pago, nombre, dias_plazo, activo FROM condicion_pago ORDER BY dias_plazo ASC";
                                    $result = $conexion->query($query);
                                    if ($result && $result->num_rows > 0) {
                                        while ($row = $result->fetch_assoc()) {
                                            $estadoBadge = $row['activo']
                                                ? "<span style='background-color:#d1e7dd;color:#0f5132;padding:6px 12px;border-radius:20px;font-weight:500;'>\n                 <i class=\"fas fa-circle me-1\" style=\"font-size:8px;color:#198754;\"></i>Activo\n               </span>"
                                                : "<span style='background-color:#f8d7da;color:#842029;padding:6px 12px;border-radius:20px;font-weight:500;'>\n                 <i class=\"fas fa-circle me-1\" style=\"font-size:8px;color:#dc3545;\"></i>Inactivo\n               </span>";
                                            $plazoClass = ((int)$row['dias_plazo'] > 60) ? 'text-warning fw-semibold' : '';
                                            echo "<tr data-activo='{$row['activo']}' data-plazo='{$row['dias_plazo']}'>";
                                            echo "<td>" . htmlspecialchars($row['id_condiciones_pago']) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['nombre']) . "</td>";
                                            echo "<td class='{$plazoClass}'><i class='fa-regular fa-clock me-1'></i>" . (int)$row['dias_plazo'] . " días</td>";
                                            echo "<td>{$estadoBadge}</td>";
                                            echo "</tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='4' class='text-center py-4'>
                                                <i class='fa-solid fa-inbox fa-2x text-muted mb-3'></i><br>
                                                <span class='text-muted'>No se encontraron condiciones registradas</span>
                                              </td></tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='4' class='text-center py-4 text-danger'>
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
    const filtroEstado = document.getElementById('filtroEstado');
    const filtroPlazo = document.getElementById('filtroPlazo');
    const resultCount = document.getElementById('resultCount');
    function aplicarFiltros() {
        const texto = searchInput.value.toLowerCase();
        const estado = filtroEstado.value; // '' | '1' | '0'
        const plazoMax = filtroPlazo.value !== '' ? parseInt(filtroPlazo.value, 10) : null;
        const rows = document.querySelectorAll('#condicionesTable tbody tr');
        let visibles = 0;
        rows.forEach(row => {
            if (row.cells.length < 4) return;
            const nombre = row.cells[1].textContent.toLowerCase();
            const activo = row.getAttribute('data-activo');
            const plazo = parseInt(row.getAttribute('data-plazo'), 10);
            let show = nombre.includes(texto);
            if (estado !== '' && activo !== estado) {
                show = false;
            }
            if (plazoMax !== null && plazo > plazoMax) {
                show = false;
            }
            row.style.display = show ? '' : 'none';
            if (show) visibles++;
        });
        if (texto === '' && estado === '' && filtroPlazo.value === '') {
            resultCount.textContent = rows.length + ' condiciones encontradas';
        } else {
            resultCount.textContent = visibles + ' condiciones encontradas (filtradas)';
        }
    }
    searchInput.addEventListener('keyup', aplicarFiltros);
    filtroEstado.addEventListener('change', aplicarFiltros);
    filtroPlazo.addEventListener('keyup', aplicarFiltros);
    filtroPlazo.addEventListener('change', aplicarFiltros);
    searchInput.focus();
});
</script>
<?php include '../includes/footer.php'; ?>