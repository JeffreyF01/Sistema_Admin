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
                        <i class="fa-solid fa-magnifying-glass me-2"></i>Consulta de Monedas
                    </h4>
                    <p class="page-subtitle">Visualiza las monedas configuradas y su tasa de cambio</p>
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
                    <h5 class="mb-0"><i class="fa-solid fa-magnifying-glass me-2"></i>Buscar Monedas</h5>
                    <span class="text-muted small" id="resultCount">
                        <?php
                        if (isset($conexion) && $conexion instanceof mysqli) {
                            $countQuery = "SELECT COUNT(*) as total FROM moneda";
                            $countResult = $conexion->query($countQuery);
                            if ($countResult && $countResult->num_rows > 0) {
                                $countRow = $countResult->fetch_assoc();
                                echo $countRow['total'] . " monedas encontradas";
                            }
                        }
                        ?>
                    </span>
                </div>
                <div class="card-body">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-6">
                            <label for="searchInput" class="form-label text-required">Código, Nombre o Símbolo</label>
                            <input type="text" class="form-control form-control-custom" id="searchInput" placeholder="Ej: USD, Dólar, $ ...">
                        </div>
                        <div class="col-md-3">
                            <label for="filtroEstado" class="form-label">Estado</label>
                            <select id="filtroEstado" class="form-select form-control-custom">
                                <option value="">Todos</option>
                                <option value="1">Activos</option>
                                <option value="0">Inactivos</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="filtroBase" class="form-label">Moneda Base</label>
                            <select id="filtroBase" class="form-select form-control-custom">
                                <option value="">Todas</option>
                                <option value="1">Solo Base</option>
                                <option value="0">No Base</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card card-custom fade-in">
                <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fa-solid fa-list me-2"></i>Lista de Monedas</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-mantenimiento" id="monedasTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Código</th>
                                    <th>Nombre</th>
                                    <th>Símbolo</th>
                                    <th>Tasa Cambio</th>
                                    <th>Base</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if (isset($conexion) && $conexion instanceof mysqli) {
                                    $query = "SELECT id_monedas, codigo, nombre, simbolo, tasa_cambio, es_base, activo FROM moneda ORDER BY nombre ASC";
                                    $result = $conexion->query($query);
                                    if ($result && $result->num_rows > 0) {
                                        while ($row = $result->fetch_assoc()) {
                                            $estadoBadge = $row['activo']
                                                ? "<span style='background-color:#d1e7dd;color:#0f5132;padding:6px 12px;border-radius:20px;font-weight:500;'>\n                 <i class=\"fas fa-circle me-1\" style=\"font-size:8px;color:#198754;\"></i>Activo\n               </span>"
                                                : "<span style='background-color:#f8d7da;color:#842029;padding:6px 12px;border-radius:20px;font-weight:500;'>\n                 <i class=\"fas fa-circle me-1\" style=\"font-size:8px;color:#dc3545;\"></i>Inactivo\n               </span>";
                                            $baseTxt = $row['es_base'] == 1 ? "<span class='badge bg-primary'>Sí</span>" : "<span class='badge bg-secondary'>No</span>";
                                            echo "<tr data-activo='{$row['activo']}' data-base='{$row['es_base']}'>";
                                            echo "<td>" . htmlspecialchars($row['id_monedas']) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['codigo']) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['nombre']) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['simbolo']) . "</td>";
                                            echo "<td>" . number_format($row['tasa_cambio'], 2) . "</td>";
                                            echo "<td>{$baseTxt}</td>";
                                            echo "<td>{$estadoBadge}</td>";
                                            echo "</tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='7' class='text-center py-4'>
                                                <i class='fa-solid fa-inbox fa-2x text-muted mb-3'></i><br>
                                                <span class='text-muted'>No se encontraron monedas registradas</span>
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
    const filtroEstado = document.getElementById('filtroEstado');
    const filtroBase = document.getElementById('filtroBase');
    const resultCount = document.getElementById('resultCount');
    function aplicarFiltros() {
        const texto = searchInput.value.toLowerCase();
        const estado = filtroEstado.value; // '' | '1' | '0'
        const base = filtroBase.value; // '' | '1' | '0'
        const rows = document.querySelectorAll('#monedasTable tbody tr');
        let visibles = 0;
        rows.forEach(row => {
            if (row.cells.length < 7) return;
            const codigo = row.cells[1].textContent.toLowerCase();
            const nombre = row.cells[2].textContent.toLowerCase();
            const simbolo = row.cells[3].textContent.toLowerCase();
            const activo = row.getAttribute('data-activo');
            const esBase = row.getAttribute('data-base');
            let show = codigo.includes(texto) || nombre.includes(texto) || simbolo.includes(texto);
            if (estado !== '' && activo !== estado) {
                show = false;
            }
            if (base !== '' && esBase !== base) {
                show = false;
            }
            row.style.display = show ? '' : 'none';
            if (show) visibles++;
        });
        if (texto === '' && estado === '' && base === '') {
            resultCount.textContent = rows.length + ' monedas encontradas';
        } else {
            resultCount.textContent = visibles + ' monedas encontradas (filtradas)';
        }
    }
    searchInput.addEventListener('keyup', aplicarFiltros);
    filtroEstado.addEventListener('change', aplicarFiltros);
    filtroBase.addEventListener('change', aplicarFiltros);
    searchInput.focus();
});
</script>
<?php include '../includes/footer.php'; ?>