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
                        <i class="fa-solid fa-magnifying-glass me-2"></i>Consulta de Empresas
                    </h4>
                    <p class="page-subtitle">Visualiza las empresas configuradas en el sistema</p>
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
                    <h5 class="mb-0"><i class="fa-solid fa-magnifying-glass me-2"></i>Buscar Empresas</h5>
                    <span class="text-muted small" id="resultCount">
                        <?php
                        if (isset($conexion) && $conexion instanceof mysqli) {
                            $countQuery = "SELECT COUNT(*) as total FROM empresa";
                            $countResult = $conexion->query($countQuery);
                            if ($countResult && $countResult->num_rows > 0) {
                                $countRow = $countResult->fetch_assoc();
                                echo $countRow['total'] . " empresas encontradas";
                            }
                        }
                        ?>
                    </span>
                </div>
                <div class="card-body">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-6">
                            <label for="searchInput" class="form-label text-required">Nombre, RNC, Email o Teléfono</label>
                            <input type="text" class="form-control form-control-custom" id="searchInput" placeholder="Ej: Mi Empresa, 123456789, correo@dominio.com...">
                        </div>
                    </div>
                </div>
            </div>
            <div class="card card-custom fade-in">
                <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fa-solid fa-list me-2"></i>Lista de Empresas</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-mantenimiento" id="empresasTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>RNC</th>
                                    <th>Teléfono</th>
                                    <th>Email</th>
                                    <th>Moneda Base</th>
                                    <th>Logo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if (isset($conexion) && $conexion instanceof mysqli) {
                                    $query = "SELECT e.id_empresa, e.nombre, e.rnc, e.telefono, e.email, e.logo_url, m.nombre AS moneda
                                              FROM empresa e
                                              LEFT JOIN moneda m ON e.moneda_base_id = m.id_monedas
                                              ORDER BY e.nombre ASC";
                                    $result = $conexion->query($query);
                                    if ($result && $result->num_rows > 0) {
                                        while ($row = $result->fetch_assoc()) {
                                            $logo = $row['logo_url'] ? "<img src='".htmlspecialchars($row['logo_url'])."' alt='logo' style='height:40px;border-radius:4px;object-fit:contain;background:#fff;padding:2px;border:1px solid #ddd;'>" : "—";
                                            echo "<tr>";
                                            echo "<td>" . htmlspecialchars($row['id_empresa']) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['nombre']) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['rnc']) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['telefono']) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['moneda']) . "</td>";
                                            echo "<td>$logo</td>";
                                            echo "</tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='7' class='text-center py-4'>
                                                <i class='fa-solid fa-inbox fa-2x text-muted mb-3'></i><br>
                                                <span class='text-muted'>No se encontraron empresas registradas</span>
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
    function aplicarFiltros() {
        const texto = searchInput.value.toLowerCase();
        const rows = document.querySelectorAll('#empresasTable tbody tr');
        let visibles = 0;
        rows.forEach(row => {
            if (row.cells.length < 7) return;
            const nombre = row.cells[1].textContent.toLowerCase();
            const rnc = row.cells[2].textContent.toLowerCase();
            const telefono = row.cells[3].textContent.toLowerCase();
            const email = row.cells[4].textContent.toLowerCase();
            let show = nombre.includes(texto) || rnc.includes(texto) || telefono.includes(texto) || email.includes(texto);
            row.style.display = show ? '' : 'none';
            if (show) visibles++;
        });
        if (texto === '') {
            resultCount.textContent = rows.length + ' empresas encontradas';
        } else {
            resultCount.textContent = visibles + ' empresas encontradas (filtradas)';
        }
    }
    searchInput.addEventListener('keyup', aplicarFiltros);
    searchInput.focus();
});
</script>
<?php include '../includes/footer.php'; ?>