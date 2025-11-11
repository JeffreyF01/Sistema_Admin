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
                        <i class="fa-solid fa-magnifying-glass me-2"></i>Consulta de Ubicaciones
                    </h4>
                    <p class="page-subtitle">Visualiza las ubicaciones registradas en el sistema</p>
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
                    <h5 class="mb-0"><i class="fa-solid fa-magnifying-glass me-2"></i>Buscar Ubicaciones</h5>
                    <span class="text-muted small" id="resultCount">
                        <?php
                        if (isset($conexion) && $conexion instanceof mysqli) {
                            $countQuery = "SELECT COUNT(*) as total FROM ubicacion";
                            $countResult = $conexion->query($countQuery);
                            if ($countResult && $countResult->num_rows > 0) {
                                $countRow = $countResult->fetch_assoc();
                                echo $countRow['total'] . " ubicaciones encontradas";
                            }
                        }
                        ?>
                    </span>
                </div>
                <div class="card-body">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-6">
                            <label for="searchInput" class="form-label text-required">Código, Nombre o Almacén</label>
                            <input type="text" class="form-control form-control-custom" id="searchInput" placeholder="Ej: A-01, Andén, Principal...">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-custom fade-in">
                <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fa-solid fa-list me-2"></i>Lista de Ubicaciones</h5>
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
                        <table class="table table-mantenimiento" id="ubicacionTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Código</th>
                                    <th>Nombre</th>
                                    <th>Almacén</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if (isset($conexion) && $conexion instanceof mysqli) {
                                    $query = "SELECT u.id_ubicaciones, u.codigo, u.nombre, u.activo, a.nombre AS almacen_nombre
                                              FROM ubicacion u
                                              LEFT JOIN almacen a ON u.id_almacen = a.id_almacen
                                              ORDER BY u.nombre ASC";
                                    $result = $conexion->query($query);
                                    if ($result && $result->num_rows > 0) {
                                        while ($row = $result->fetch_assoc()) {
                                            $estadoBadge = $row['activo']
                                                ? "<span style='background-color:#d1e7dd;color:#0f5132;padding:6px 12px;border-radius:20px;font-weight:500;'>\n                 <i class=\"fas fa-circle me-1\" style=\"font-size:8px;color:#198754;\"></i>Activo\n               </span>"
                                                : "<span style='background-color:#f8d7da;color:#842029;padding:6px 12px;border-radius:20px;font-weight:500;'>\n                 <i class=\"fas fa-circle me-1\" style=\"font-size:8px;color:#dc3545;\"></i>Inactivo\n               </span>";
                                            echo "<tr data-activo='{$row['activo']}'>";
                                            echo "<td>" . htmlspecialchars($row['id_ubicaciones']) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['codigo']) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['nombre']) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['almacen_nombre']) . "</td>";
                                            echo "<td>{$estadoBadge}</td>";
                                            echo "</tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='5' class='text-center py-4'>
                                                <i class='fa-solid fa-inbox fa-2x text-muted mb-3'></i><br>
                                                <span class='text-muted'>No se encontraron ubicaciones registradas</span>
                                              </td></tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='5' class='text-center py-4 text-danger'>
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

    function aplicarFiltros() {
        const texto = searchInput.value.toLowerCase();
        const estado = filtroEstado.value; // '' | '1' | '0'
        const rows = document.querySelectorAll('#ubicacionTable tbody tr');
        let visibles = 0;
        rows.forEach(row => {
            if (row.cells.length < 5) return;
            const codigo = row.cells[1].textContent.toLowerCase();
            const nombre = row.cells[2].textContent.toLowerCase();
            const almacen = row.cells[3].textContent.toLowerCase();
            const activo = row.getAttribute('data-activo');

            let show = codigo.includes(texto) || nombre.includes(texto) || almacen.includes(texto);
            if (estado !== '' && activo !== estado) {
                show = false;
            }
            row.style.display = show ? '' : 'none';
            if (show) visibles++;
        });

        if (texto === '' && estado === '') {
            resultCount.textContent = rows.length + ' ubicaciones encontradas';
        } else {
            resultCount.textContent = visibles + ' ubicaciones encontradas (filtradas)';
        }
    }

    searchInput.addEventListener('keyup', aplicarFiltros);
    filtroEstado.addEventListener('change', aplicarFiltros);
    searchInput.focus();
});
</script>
<?php include '../includes/footer.php'; ?>
