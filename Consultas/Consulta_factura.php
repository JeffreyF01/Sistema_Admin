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
                                            
                                            echo "<tr data-id='{$row['id_facturas']}' data-activo='{$row['activo']}' data-fecha='{$row['fecha']}'>";
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
        // Modal para detalle
        const detalleModalHtml = `
        <div class="modal fade" id="detalleFacturaModal" tabindex="-1" aria-labelledby="detalleFacturaLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="detalleFacturaLabel"><i class="fa-solid fa-file-invoice me-2"></i>Detalle de Factura</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-2 mb-3">
                            <div class="col-md-4"><strong>N°:</strong> <span id="df-numero"></span></div>
                            <div class="col-md-4"><strong>Fecha:</strong> <span id="df-fecha"></span></div>
                            <div class="col-md-4"><strong>Usuario:</strong> <span id="df-usuario"></span></div>
                            <div class="col-md-6"><strong>Cliente:</strong> <span id="df-cliente"></span></div>
                            <div class="col-md-6"><strong>Condición:</strong> <span id="df-condicion"></span></div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Producto</th>
                                        <th style="width: 15%;" class="text-end">Cantidad</th>
                                        <th style="width: 20%;" class="text-end">Precio Unit.</th>
                                        <th style="width: 20%;" class="text-end">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody id="df-detalle"></tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-end mt-2">
                            <h5>Total: <span id="df-total" class="text-primary"></span></h5>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="button" class="btn btn-primary" id="df-imprimir"><i class="fa-solid fa-print me-2"></i>Imprimir</button>
                    </div>
                </div>
            </div>
        </div>`;
        document.body.insertAdjacentHTML('beforeend', detalleModalHtml);

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

    // Click para ver detalle
    document.querySelector('#facturaTable tbody')?.addEventListener('click', function(e){
        const tr = e.target.closest('tr');
        if (!tr) return;
        const id = tr.getAttribute('data-id');
        if (!id) return;
        cargarDetalle(id);
    });

    // Soporte para abrir por parámetro ?id=123
    const params = new URLSearchParams(window.location.search);
    const idParam = params.get('id');
    if (idParam) {
        cargarDetalle(idParam);
    }

    function cargarDetalle(id){
        fetch('../Procesos/Factura_ajax.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ accion: 'obtener', id: parseInt(id, 10) })
        })
        .then(r => r.json())
        .then(resp => {
            if (!resp.success) throw new Error(resp.message || 'Error al obtener la factura');
            const f = resp.data;
            document.getElementById('df-numero').textContent = f.numero_documento;
            document.getElementById('df-fecha').textContent = new Date(f.fecha).toLocaleDateString();
            document.getElementById('df-usuario').textContent = f.usuario_nombre || '';
            document.getElementById('df-cliente').textContent = `${f.cliente_nombre} (${f.doc_identidad || ''})`;
            document.getElementById('df-condicion').textContent = `${f.condicion_nombre} ${f.dias_plazo ? '('+f.dias_plazo+' días)' : ''}`;

            const tbody = document.getElementById('df-detalle');
            tbody.innerHTML = '';
            let total = 0;
            (f.detalle || []).forEach(it => {
                const cantidad = parseFloat(it.cantidad) || 0;
                const precio = parseFloat(it.precio_unitario) || 0;
                const subtotal = cantidad * precio;
                total += subtotal;
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${it.producto_nombre || ''}</td>
                    <td class="text-end">${cantidad.toLocaleString(undefined,{maximumFractionDigits:2})}</td>
                    <td class="text-end">$${precio.toFixed(2)}</td>
                    <td class="text-end">$${subtotal.toFixed(2)}</td>
                `;
                tbody.appendChild(tr);
            });
            document.getElementById('df-total').textContent = `$${total.toFixed(2)}`;

            const modalEl = document.getElementById('detalleFacturaModal');
            const bsModal = new bootstrap.Modal(modalEl);
            bsModal.show();

            document.getElementById('df-imprimir').onclick = () => {
                window.open(`../Procesos/Factura_imprimir.php?id=${id}`, '_blank');
            };
        })
        .catch(err => {
            console.error(err);
            alert(err.message || 'Error al obtener el detalle de la factura');
        });
    }
});
</script>
<?php include '../includes/footer.php'; ?>
