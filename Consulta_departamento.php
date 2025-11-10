<?php
session_start();
if(!isset($_SESSION['usuario'])) {
    header("Location: index.html");
    exit();
}
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
                        <i class="fa-solid fa-magnifying-glass me-2"></i>Consulta de Departamentos
                    </h4>
                    <p class="page-subtitle">Visualiza los departamentos registrados en el sistema</p>
                </div>
                <div class="col-auto">
                    <div class="user-info">
                        <div class="user-avatar">
                            <?php echo strtoupper(substr($_SESSION['usuario'], 0, 1)); ?>
                        </div>
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
                    <h5 class="mb-0"><i class="fa-solid fa-magnifying-glass me-2"></i>Buscar Departamentos</h5>
                    <span class="text-muted small" id="resultCount">
                        <?php
                        if (isset($conexion) && $conexion instanceof mysqli) {
                            $countQuery = "SELECT COUNT(*) as total FROM departamento";
                            $countResult = $conexion->query($countQuery);
                            if ($countResult && $countResult->num_rows > 0) {
                                $countRow = $countResult->fetch_assoc();
                                echo $countRow['total'] . " departamentos encontrados";
                            }
                        }
                        ?>
                    </span>
                </div>
                <div class="card-body">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-6">
                            <label for="searchInput" class="form-label text-required">Nombre del Departamento</label>
                            <input type="text" class="form-control form-control-custom" id="searchInput" placeholder="Ej: Ventas, Tecnología, RRHH...">
                        </div>
                        
                    </div>
                </div>
            </div>

            <!-- Tabla de resultados -->
            <div class="card card-custom fade-in">
                <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fa-solid fa-list me-2"></i>Lista de Departamentos</h5>
                    <div class="input-group input-group-sm" style="max-width:200px">
                        <span class="input-group-text bg-primary text-white"><i class="fa-solid fa-filter"></i></span>
                        <select id="filtroEstado" class="form-select">
                            <option value="">Todos</option>
                            <option value="1">Activos</option>
                            <option value="0">Inactivos</option>
                        </select>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-mantenimiento" id="departamentoTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre del Departamento</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if (isset($conexion) && $conexion instanceof mysqli) {
                                    $query = "SELECT id_departamentos, nombre, activo FROM departamento ORDER BY nombre ASC";
                                    $result = $conexion->query($query);
                                    if ($result && $result->num_rows > 0) {
                                        while ($row = $result->fetch_assoc()) {
                                            $estadoBadge = $row['activo'] ? 
                                                "<span class='badge bg-success badge-estado'><i class='fa-solid fa-circle-check me-1'></i>Activo</span>" : 
                                                "<span class='badge bg-danger badge-estado'><i class='fa-solid fa-circle-xmark me-1'></i>Inactivo</span>";
                                            echo "<tr data-activo='{$row['activo']}'>";
                                            echo "<td><strong>#" . htmlspecialchars($row['id_departamentos']) . "</strong></td>";
                                            echo "<td>" . htmlspecialchars($row['nombre']) . "</td>";
                                            echo "<td>{$estadoBadge}</td>";
                                            echo "</tr>";
                                        }
                                    } else {
                    echo "<tr><td colspan='3' class='text-center py-4'>
                        <i class='fa-solid fa-inbox fa-2x text-muted mb-3'></i><br>
                                                <span class='text-muted'>No se encontraron departamentos registrados</span>
                                              </td></tr>";
                                    }
                                } else {
                    echo "<tr><td colspan='3' class='text-center py-4 text-danger'>
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
    // Botones eliminados (limpiar / exportar)

    function aplicarFiltros() {
        const texto = searchInput.value.toLowerCase();
        const estado = filtroEstado.value; // '' | '1' | '0'
        const rows = document.querySelectorAll('#departamentoTable tbody tr');
        let visibles = 0;
        rows.forEach(row => {
            if (row.cells.length < 3) return; // mensajes
            const nombre = row.cells[1].textContent.toLowerCase();
            const activo = row.getAttribute('data-activo');
            let show = nombre.includes(texto);
            if (estado !== '' && activo !== estado) {
                show = false;
            }
            row.style.display = show ? '' : 'none';
            if (show) visibles++;
        });
        if (texto === '' && estado === '') {
            resultCount.textContent = rows.length + ' departamentos encontrados';
        } else {
            resultCount.textContent = visibles + ' departamentos encontrados (filtrados)';
        }
    }

    searchInput.addEventListener('keyup', aplicarFiltros);
    filtroEstado.addEventListener('change', aplicarFiltros);
    // Eliminados eventos de limpiar y exportar

    // Focus inicial
    searchInput.focus();
});
</script>
<?php include 'includes/footer.php'; ?>