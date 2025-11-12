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
                        <i class="fa-solid fa-list me-2"></i>Listado de Facturas
                    </h4>
                    <p class="page-subtitle">Visualiza y gestiona todas las facturas de venta</p>
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
            <!-- Filtros -->
            <div class="card card-custom fade-in mb-4">
                <div class="card-header card-header-custom">
                    <h5 class="mb-0"><i class="fa-solid fa-filter me-2"></i>Filtros de Búsqueda</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label for="filtroNumero" class="form-label">N° Factura</label>
                            <input type="text" class="form-control form-control-custom" id="filtroNumero" placeholder="FAC-00000001">
                        </div>
                        <div class="col-md-3">
                            <label for="filtroCliente" class="form-label">Cliente</label>
                            <input type="text" class="form-control form-control-custom" id="filtroCliente" placeholder="Nombre del cliente">
                        </div>
                        <div class="col-md-2">
                            <label for="filtroFechaDesde" class="form-label">Desde</label>
                            <input type="date" class="form-control form-control-custom" id="filtroFechaDesde">
                        </div>
                        <div class="col-md-2">
                            <label for="filtroFechaHasta" class="form-label">Hasta</label>
                            <input type="date" class="form-control form-control-custom" id="filtroFechaHasta">
                        </div>
                        <div class="col-md-2">
                            <label for="filtroEstado" class="form-label">Estado</label>
                            <select class="form-select form-control-custom" id="filtroEstado">
                                <option value="">Todos</option>
                                <option value="1" selected>Activos</option>
                                <option value="0">Anulados</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botones de acción -->
            <div class="mb-3">
                <a href="MantFacturacion.php" class="btn btn-primary">
                    <i class="fa-solid fa-plus me-2"></i>Nueva Factura
                </a>
                <button type="button" class="btn btn-success" id="exportarExcel">
                    <i class="fa-solid fa-file-excel me-2"></i>Exportar a Excel
                </button>
            </div>

            <!-- Tabla de Facturas -->
            <div class="card card-custom fade-in">
                <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fa-solid fa-file-invoice me-2"></i>Facturas Registradas</h5>
                    <span class="badge bg-primary" id="contadorFacturas">0 facturas</span>
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
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
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
                                        
                                        echo "<tr data-activo='{$row['activo']}' data-cliente='" . htmlspecialchars($row['cliente_nombre']) . "' data-numero='" . htmlspecialchars($row['numero_documento']) . "' data-fecha='" . htmlspecialchars($row['fecha']) . "'>";
                                        echo "<td><strong>" . htmlspecialchars($row['numero_documento']) . "</strong></td>";
                                        echo "<td>" . htmlspecialchars($row['cliente_nombre']) . "</td>";
                                        echo "<td>" . date('d/m/Y', strtotime($row['fecha'])) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['condicion_nombre']) . "</td>";
                                        echo "<td><strong>$" . number_format($row['total'], 2) . "</strong></td>";
                                        echo "<td>" . htmlspecialchars($row['usuario_nombre']) . "</td>";
                                        echo "<td>{$estadoBadge}</td>";
                                        echo "<td>
                                                <button class='btn btn-sm btn-info' onclick='verDetalle({$row['id_facturas']})' title='Ver Detalle'>
                                                    <i class='fa-solid fa-eye'></i>
                                                </button>
                                                <button class='btn btn-sm btn-primary' onclick='imprimirFactura({$row['id_facturas']})' title='Imprimir'>
                                                    <i class='fa-solid fa-print'></i>
                                                </button>";
                                        
                                        if ($row['activo'] == 1) {
                                            echo "<button class='btn btn-sm btn-danger' onclick='anularFactura({$row['id_facturas']})' title='Anular'>
                                                    <i class='fa-solid fa-ban'></i>
                                                  </button>";
                                        }
                                        
                                        echo "</td></tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='8' class='text-center py-4'>
                                            <i class='fa-solid fa-inbox fa-2x text-muted mb-3'></i><br>
                                            <span class='text-muted'>No se encontraron facturas registradas</span>
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

<!-- Modal para Ver Detalle -->
<div class="modal fade" id="detalleModal" tabindex="-1" aria-labelledby="detalleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detalleModalLabel">
                    <i class="fa-solid fa-file-invoice me-2"></i>Detalle de Factura
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="detalleContent">
                <!-- Contenido dinámico -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" id="imprimirDesdeDetalle">
                    <i class="fa-solid fa-print me-2"></i>Imprimir
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    let facturaIdActual = null;

    // Aplicar filtros
    $('#filtroNumero, #filtroCliente, #filtroFechaDesde, #filtroFechaHasta, #filtroEstado').on('change keyup', function() {
        aplicarFiltros();
    });

    function aplicarFiltros() {
        const numero = $('#filtroNumero').val().toLowerCase();
        const cliente = $('#filtroCliente').val().toLowerCase();
        const fechaDesde = $('#filtroFechaDesde').val();
        const fechaHasta = $('#filtroFechaHasta').val();
        const estado = $('#filtroEstado').val();

        let visibles = 0;

        $('#facturaTable tbody tr').each(function() {
            if ($(this).find('td').length < 8) return;

            const row = $(this);
            const rowNumero = row.data('numero').toLowerCase();
            const rowCliente = row.data('cliente').toLowerCase();
            const rowFecha = row.data('fecha');
            const rowActivo = row.data('activo').toString();

            let mostrar = true;

            if (numero && !rowNumero.includes(numero)) mostrar = false;
            if (cliente && !rowCliente.includes(cliente)) mostrar = false;
            if (fechaDesde && rowFecha < fechaDesde) mostrar = false;
            if (fechaHasta && rowFecha > fechaHasta) mostrar = false;
            if (estado !== '' && rowActivo !== estado) mostrar = false;

            if (mostrar) {
                row.show();
                visibles++;
            } else {
                row.hide();
            }
        });

        $('#contadorFacturas').text(visibles + ' factura(s)');
    }

    // Contar facturas iniciales
    aplicarFiltros();

    // Ver detalle
    window.verDetalle = function(id) {
        facturaIdActual = id;
        $.ajax({
            url: 'Factura_ajax.php',
            type: 'POST',
            data: { accion: 'obtener', id: id },
            success: function(response) {
                if (response.success) {
                    mostrarDetalle(response.data);
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            }
        });
    };

    function mostrarDetalle(factura) {
        let html = `
            <div class="row mb-3">
                <div class="col-md-6">
                    <p><strong>N° Factura:</strong> ${factura.numero_documento}</p>
                    <p><strong>Cliente:</strong> ${factura.cliente_nombre}</p>
                    <p><strong>Documento:</strong> ${factura.doc_identidad || 'N/A'}</p>
                    <p><strong>Teléfono:</strong> ${factura.telefono || 'N/A'}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Fecha:</strong> ${new Date(factura.fecha).toLocaleDateString('es-DO')}</p>
                    <p><strong>Condición:</strong> ${factura.condicion_nombre}</p>
                    <p><strong>Usuario:</strong> ${factura.usuario_nombre}</p>
                    <p><strong>Estado:</strong> ${factura.activo == 1 ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Anulado</span>'}</p>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-sm table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>SKU</th>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Precio Unit.</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>`;

        factura.detalle.forEach(function(item) {
            const subtotal = item.cantidad * item.precio_unitario;
            html += `<tr>
                <td>${item.sku}</td>
                <td>${item.producto_nombre}</td>
                <td>${item.cantidad}</td>
                <td>$${parseFloat(item.precio_unitario).toFixed(2)}</td>
                <td>$${subtotal.toFixed(2)}</td>
            </tr>`;
        });

        html += `</tbody>
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="4" class="text-end"><strong>TOTAL:</strong></td>
                            <td><strong>$${parseFloat(factura.total).toFixed(2)}</strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>`;

        $('#detalleContent').html(html);
        $('#detalleModal').modal('show');
    }

    // Imprimir desde detalle
    $('#imprimirDesdeDetalle').click(function() {
        if (facturaIdActual) {
            window.open('Factura_imprimir.php?id=' + facturaIdActual, '_blank');
        }
    });

    // Imprimir factura
    window.imprimirFactura = function(id) {
        window.open('Factura_imprimir.php?id=' + id, '_blank');
    };

    // Anular factura
    window.anularFactura = function(id) {
        Swal.fire({
            title: '¿Está seguro?',
            text: 'Esta acción anulará la factura y devolverá el inventario',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, anular',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'Factura_ajax.php',
                    type: 'POST',
                    data: { accion: 'anular', id: id },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Anulado', response.message, 'success');
                            setTimeout(function() {
                                location.reload();
                            }, 1500);
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    }
                });
            }
        });
    };

    // Exportar a Excel (simulado)
    $('#exportarExcel').click(function() {
        Swal.fire('Información', 'Funcionalidad de exportación en desarrollo', 'info');
    });
});
</script>
<?php include '../includes/footer.php'; ?>
