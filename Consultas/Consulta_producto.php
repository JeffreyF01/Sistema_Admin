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
                        <i class="fa-solid fa-magnifying-glass me-2"></i>Consulta de Productos
                    </h4>
                    <p class="page-subtitle">Visualiza los productos registrados en el sistema</p>
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
                    <h5 class="mb-0"><i class="fa-solid fa-magnifying-glass me-2"></i>Buscar Productos</h5>
                    <span class="text-muted small" id="resultCount">
                        <?php
                        if (isset($conexion) && $conexion instanceof mysqli) {
                            $countQuery = "SELECT COUNT(*) as total FROM producto";
                            $countResult = $conexion->query($countQuery);
                            if ($countResult && $countResult->num_rows > 0) {
                                $countRow = $countResult->fetch_assoc();
                                echo $countRow['total'] . " productos encontrados";
                            }
                        }
                        ?>
                    </span>
                </div>
                <div class="card-body">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-6">
                            <label for="searchInput" class="form-label text-required">SKU, Nombre, Departamento o Grupo</label>
                            <input type="text" class="form-control form-control-custom" id="searchInput" placeholder="Ej: SKU001, Laptop, Tecnología...">
                        </div>
                    </div>
                </div>
            </div>
            <div class="card card-custom fade-in">
                <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fa-solid fa-list me-2"></i>Lista de Productos</h5>
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
                        <table class="table table-mantenimiento" id="productosTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>SKU</th>
                                    <th>Nombre</th>
                                    <th>Departamento</th>
                                    <th>Grupo</th>
                                    <th>Stock</th>
                                    <th>Stock Mín.</th>
                                    <th>Precio Venta</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if (isset($conexion) && $conexion instanceof mysqli) {
                                    $query = "SELECT 
                                                p.id_productos, p.sku, p.nombre, p.stock, p.stock_min, p.precio_venta, p.activo,
                                                d.nombre AS departamento, g.nombre AS grupo
                                              FROM producto p
                                              LEFT JOIN departamento d ON p.departamento_id = d.id_departamentos
                                              LEFT JOIN grupo g ON p.grupo_id = g.id_grupos
                                              ORDER BY p.nombre ASC";
                                    $result = $conexion->query($query);
                                    if ($result && $result->num_rows > 0) {
                                        while ($row = $result->fetch_assoc()) {
                                            $estadoBadge = $row['activo']
                                                ? "<span style='background-color:#d1e7dd;color:#0f5132;padding:6px 12px;border-radius:20px;font-weight:500;'>\n                 <i class=\"fas fa-circle me-1\" style=\"font-size:8px;color:#198754;\"></i>Activo\n               </span>"
                                                : "<span style='background-color:#f8d7da;color:#842029;padding:6px 12px;border-radius:20px;font-weight:500;'>\n                 <i class=\"fas fa-circle me-1\" style=\"font-size:8px;color:#dc3545;\"></i>Inactivo\n               </span>";
                                            
                                            // Alerta de stock bajo
                                            $stockClass = '';
                                            if ($row['stock'] <= $row['stock_min']) {
                                                $stockClass = 'text-danger fw-bold';
                                            }
                                            
                                            echo "<tr data-activo='{$row['activo']}'>";
                                            echo "<td>" . htmlspecialchars($row['id_productos']) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['sku']) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['nombre']) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['departamento']) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['grupo']) . "</td>";
                                            echo "<td class='{$stockClass}'>" . number_format($row['stock'], 0) . "</td>";
                                            echo "<td>" . number_format($row['stock_min'], 0) . "</td>";
                                            echo "<td>$" . number_format($row['precio_venta'], 2) . "</td>";
                                            echo "<td>{$estadoBadge}</td>";
                                            echo "</tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='9' class='text-center py-4'>
                                                <i class='fa-solid fa-inbox fa-2x text-muted mb-3'></i><br>
                                                <span class='text-muted'>No se encontraron productos registrados</span>
                                              </td></tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='9' class='text-center py-4 text-danger'>
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
        const rows = document.querySelectorAll('#productosTable tbody tr');
        let visibles = 0;
        rows.forEach(row => {
            if (row.cells.length < 9) return;
            const sku = row.cells[1].textContent.toLowerCase();
            const nombre = row.cells[2].textContent.toLowerCase();
            const departamento = row.cells[3].textContent.toLowerCase();
            const grupo = row.cells[4].textContent.toLowerCase();
            const activo = row.getAttribute('data-activo');
            let show = sku.includes(texto) || nombre.includes(texto) || departamento.includes(texto) || grupo.includes(texto);
            if (estado !== '' && activo !== estado) {
                show = false;
            }
            row.style.display = show ? '' : 'none';
            if (show) visibles++;
        });
        if (texto === '' && estado === '') {
            resultCount.textContent = rows.length + ' productos encontrados';
        } else {
            resultCount.textContent = visibles + ' productos encontrados (filtrados)';
        }
    }
    searchInput.addEventListener('keyup', aplicarFiltros);
    filtroEstado.addEventListener('change', aplicarFiltros);
    searchInput.focus();
});
</script>
<?php include '../includes/footer.php'; ?>
