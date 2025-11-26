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
                        <i class="fa-solid fa-rotate-left me-2"></i>Listado de Devoluciones
                    </h4>
                    <p class="page-subtitle">Consulta todas las devoluciones registradas en el sistema</p>
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
            <div class="row mb-3">
                <div class="col-12 d-flex justify-content-end gap-2">
                    <a href="MantDevolucion.php" class="btn btn-primary">
                        <i class="fa-solid fa-plus me-2"></i>Nueva Devolución
                    </a>
                    <a href="../dashboard.php" class="btn btn-secondary">
                        <i class="fa-solid fa-home me-2"></i>Volver al Dashboard
                    </a>
                </div>
            </div>

            <div class="card card-custom fade-in">
                <div class="card-header card-header-custom">
                    <h5 class="mb-0"><i class="fa-solid fa-table me-2"></i>Todas las Devoluciones</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="devolucionesTable">
                            <thead>
                                <tr>
                                    <th>N° Devolución</th>
                                    <th>Factura</th>
                                    <th>Cliente</th>
                                    <th>Fecha</th>
                                    <th>Total</th>
                                    <th>Usuario</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Detalle de Devolución -->
<div class="modal fade" id="detalleDevolucionModal" tabindex="-1" aria-labelledby="detalleDevolucionLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detalleDevolucionLabel"><i class="fa-solid fa-rotate-left me-2"></i>Detalle de Devolución</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-2 mb-3">
                    <div class="col-md-4"><strong>N°:</strong> <span id="dd-numero"></span></div>
                    <div class="col-md-4"><strong>Fecha:</strong> <span id="dd-fecha"></span></div>
                    <div class="col-md-4"><strong>Usuario:</strong> <span id="dd-usuario"></span></div>
                    <div class="col-md-6"><strong>Factura:</strong> <span id="dd-factura"></span></div>
                    <div class="col-md-6"><strong>Cliente:</strong> <span id="dd-cliente"></span></div>
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
                        <tbody id="dd-detalle"></tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-end mt-2">
                    <h5>Total: <span id="dd-total" class="text-primary"></span></h5>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" id="dd-imprimir"><i class="fa-solid fa-print me-2"></i>Imprimir</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    // Inicializar DataTable
    const table = $('#devolucionesTable').DataTable({
        ajax: {
            url: '../Consultas/Consulta_devolucion.php',
            dataSrc: 'data'
        },
        columns: [
            { data: 'numero_documento' },
            { data: 'factura_numero' },
            { data: 'cliente_nombre' },
            { 
                data: 'fecha',
                render: function(data) {
                    return new Date(data).toLocaleDateString();
                }
            },
            { 
                data: 'total',
                render: function(data) {
                    return '$' + parseFloat(data).toFixed(2);
                }
            },
            { data: 'usuario_nombre' },
            { 
                data: 'activo',
                render: function(data) {
                    return data == 1 
                        ? '<span class="badge bg-success">Activo</span>'
                        : '<span class="badge bg-danger">Anulado</span>';
                }
            },
            {
                data: null,
                orderable: false,
                render: function(data, type, row) {
                    let botones = `
                        <button class="btn btn-sm btn-info" onclick="verDevolucion(${row.id_devoluciones})" title="Ver Detalle">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-primary" onclick="imprimirDevolucion(${row.id_devoluciones})" title="Imprimir">
                            <i class="fa-solid fa-print"></i>
                        </button>
                    `;
                    
                    if (row.activo == 1) {
                        botones += `
                            <button class="btn btn-sm btn-danger" onclick="anularDevolucion(${row.id_devoluciones})" title="Anular">
                                <i class="fa-solid fa-ban"></i>
                            </button>
                        `;
                    }
                    
                    return botones;
                }
            }
        ],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
        },
        order: [[3, 'desc']],
        pageLength: 25,
        responsive: true
    });

    window.verDevolucion = function(id) {
        $.ajax({
            url: 'Devolucion_ajax.php',
            type: 'POST',
            data: { accion: 'obtener', id: id },
            success: function(response) {
                if (!response.success) {
                    Swal.fire('Error', response.message || 'No se pudo cargar la devolución', 'error');
                    return;
                }
                const d = response.data;
                $('#dd-numero').text(d.numero_documento);
                $('#dd-fecha').text(new Date(d.fecha).toLocaleDateString());
                $('#dd-usuario').text(d.usuario_nombre || '');
                $('#dd-factura').text(d.factura_numero || '');
                $('#dd-cliente').text(d.cliente_nombre || '');

                const $tbody = $('#dd-detalle');
                $tbody.empty();
                let total = 0;
                (d.detalle || []).forEach(function(it){
                    const cantidad = parseFloat(it.cantidad) || 0;
                    const precio = parseFloat(it.precio_unitario) || 0;
                    const subtotal = cantidad * precio;
                    total += subtotal;
                    $tbody.append(`
                        <tr>
                            <td>${it.producto_nombre || ''}</td>
                            <td class="text-end">${cantidad.toLocaleString(undefined,{maximumFractionDigits:2})}</td>
                            <td class="text-end">$${precio.toFixed(2)}</td>
                            <td class="text-end">$${subtotal.toFixed(2)}</td>
                        </tr>
                    `);
                });
                $('#dd-total').text(`$${total.toFixed(2)}`);

                $('#dd-imprimir').off('click').on('click', function(){
                    imprimirDevolucion(id);
                });

                const modal = new bootstrap.Modal(document.getElementById('detalleDevolucionModal'));
                modal.show();
            },
            error: function() {
                Swal.fire('Error', 'Error al obtener el detalle de la devolución', 'error');
            }
        });
    };

    window.imprimirDevolucion = function(id) {
        window.open('Devolucion_imprimir.php?id=' + id, '_blank');
    };

    window.anularDevolucion = function(id) {
        Swal.fire({
            title: '¿Está seguro?',
            text: 'Esta acción anulará la devolución y descontará el inventario',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, anular',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'Devolucion_ajax.php',
                    type: 'POST',
                    data: { accion: 'anular', id: id },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Anulado', response.message, 'success');
                            table.ajax.reload();
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    }
                });
            }
        });
    };
});
</script>
<?php include '../includes/footer.php'; ?>
