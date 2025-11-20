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
                        <i class="fa-solid fa-magnifying-glass me-2"></i>Consulta de Proveedores
                    </h4>
                    <p class="page-subtitle">Visualiza los proveedores registrados y su estado</p>
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
                    <h5 class="mb-0"><i class="fa-solid fa-magnifying-glass me-2"></i>Buscar Proveedores</h5>
                    <span class="text-muted small" id="resultCount">
                        <?php
                        if (isset($conexion) && $conexion instanceof mysqli) {
                            $countQuery = "SELECT COUNT(*) AS total FROM proveedor";
                            $countResult = $conexion->query($countQuery);
                            if ($countResult && $countResult->num_rows > 0) {
                                $countRow = $countResult->fetch_assoc();
                                echo (int)$countRow['total'] . " proveedores encontrados";
                            }
                        }
                        ?>
                    </span>
                </div>
                <div class="card-body">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-6">
                            <label for="searchInput" class="form-label text-required">Nombre, RNC o Email</label>
                            <input type="text" class="form-control form-control-custom" id="searchInput" placeholder="Ej: Empresa SRL, 1-01-12345-6, mail@dominio.com">
                        </div>
                    </div>
                </div>
            </div>
            <div class="card card-custom fade-in">
                <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fa-solid fa-list me-2"></i>Lista de Proveedores</h5>
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
                        <table class="table table-mantenimiento" id="proveedoresTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>RNC</th>
                                    <th>Email</th>
                                    <th>Teléfono</th>
                                    <th>Dirección</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if (isset($conexion) && $conexion instanceof mysqli) {
                                    $query = "SELECT id_proveedores, nombre, rnc, email, telefono, direccion, activo FROM proveedor ORDER BY nombre ASC";
                                    $result = $conexion->query($query);
                                    if ($result && $result->num_rows > 0) {
                                        while ($row = $result->fetch_assoc()) {
                                            $estadoBadge = $row['activo']
                                                ? "<span style='background-color:#d1e7dd;color:#0f5132;padding:6px 12px;border-radius:20px;font-weight:500;'>\n                 <i class=\"fas fa-circle me-1\" style=\"font-size:8px;color:#198754;\"></i>Activo\n               </span>"
                                                : "<span style='background-color:#f8d7da;color:#842029;padding:6px 12px;border-radius:20px;font-weight:500;'>\n                 <i class=\"fas fa-circle me-1\" style=\"font-size:8px;color:#dc3545;\"></i>Inactivo\n               </span>";
                                            echo "<tr data-activo='{$row['activo']}'>";
                                            echo "<td>" . htmlspecialchars($row['id_proveedores']) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['nombre']) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['rnc']) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['telefono']) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['direccion']) . "</td>";
                                            echo "<td>{$estadoBadge}</td>";
                                            echo "</tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='7' class='text-center py-4'>
                                                <i class='fa-solid fa-inbox fa-2x text-muted mb-3'></i><br>
                                                <span class='text-muted'>No se encontraron proveedores registrados</span>
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
    function aplicarFiltros() {
        const texto = searchInput.value.toLowerCase();
        const estado = filtroEstado.value;
        const rows = document.querySelectorAll('#proveedoresTable tbody tr');
        let visibles = 0;
        rows.forEach(row => {
            if (row.cells.length < 7) return;
            const nombre = row.cells[1].textContent.toLowerCase();
            const rnc = row.cells[2].textContent.toLowerCase();
            const email = row.cells[3].textContent.toLowerCase();
            const activo = row.getAttribute('data-activo');
            let show = nombre.includes(texto) || rnc.includes(texto) || email.includes(texto);
            if (estado !== '' && activo !== estado) {
                show = false;
            }
            row.style.display = show ? '' : 'none';
            if (show) visibles++;
        });
        if (texto === '' && estado === '') {
            resultCount.textContent = rows.length + ' proveedores encontrados';
        } else {
            resultCount.textContent = visibles + ' proveedores encontrados (filtrados)';
        }
    }
    searchInput.addEventListener('keyup', aplicarFiltros);
    filtroEstado.addEventListener('change', aplicarFiltros);
    searchInput.focus();
});
</script>
<?php include '../includes/footer.php'; ?>
