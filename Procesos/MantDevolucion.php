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
                        <i class="fa-solid fa-rotate-left me-2"></i>Devoluciones
                    </h4>
                    <p class="page-subtitle">Registra devoluciones de clientes sobre facturas emitidas</p>
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
            <!-- Botón para nueva devolución -->
            <div class="row mb-3">
                <div class="col-12 d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#devolucionModal">
                        <i class="fa-solid fa-plus me-2"></i>Nueva Devolución
                    </button>
                </div>
            </div>

            <!-- Tabla de Devoluciones Recientes -->
            <div class="card card-custom fade-in">
                <div class="card-header card-header-custom">
                    <h5 class="mb-0"><i class="fa-solid fa-rotate-left me-2"></i>Devoluciones Recientes</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-mantenimiento" id="devolucionTable">
                            <thead>
                                <tr>
                                    <th>N° Devolución</th>
                                    <th>Factura</th>
                                    <th>Cliente</th>
                                    <th>Fecha</th>
                                    <th>Total</th>
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

<!-- Modal para Nueva Devolución -->
<div class="modal fade" id="devolucionModal" tabindex="-1" aria-labelledby="devolucionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="devolucionModalLabel">
                    <i class="fa-solid fa-rotate-left me-2"></i>Nueva Devolución
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="devolucionForm">
                    <input type="hidden" id="devolucionId" name="id">
                    <input type="hidden" id="accion" name="accion" value="guardar">

                    <!-- Información de la Devolución -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <label for="numeroDocumento" class="form-label text-required">N° Documento</label>
                            <input type="text" class="form-control form-control-custom" id="numeroDocumento" name="numero_documento" required readonly>
                        </div>
                        <div class="col-md-3">
                            <label for="fecha" class="form-label text-required">Fecha</label>
                            <input type="date" class="form-control form-control-custom" id="fecha" name="fecha" required>
                        </div>
                        <div class="col-md-6">
                            <label for="facturaId" class="form-label text-required">Factura</label>
                            <select class="form-select form-control-custom" id="facturaId" name="factura_id" required>
                                <option value="">Seleccione una factura...</option>
                            </select>
                        </div>
                    </div>

                    <!-- Información de la Factura Seleccionada -->
                    <div id="facturaInfo" class="card mb-3" style="display:none;">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fa-solid fa-file-invoice me-2"></i>Información de la Factura</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-2">
                                <div class="col-md-4"><strong>Cliente:</strong> <span id="info-cliente"></span></div>
                                <div class="col-md-4"><strong>Fecha Factura:</strong> <span id="info-fecha"></span></div>
                                <div class="col-md-4"><strong>Total Factura:</strong> <span id="info-total"></span></div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabla de productos disponibles para devolver -->
                    <div class="card mb-3" id="productosSection" style="display:none;">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <h6 class="mb-0"><i class="fa-solid fa-boxes-stacked me-2"></i>Productos Disponibles para Devolución</h6>
                            <small class="text-muted">Seleccione las cantidades a devolver</small>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered align-middle" id="productosFacturaTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 5%;" class="text-center">
                                                <input type="checkbox" id="seleccionarTodos" title="Seleccionar todos">
                                            </th>
                                            <th style="width: 35%;">Producto</th>
                                            <th style="width: 12%;" class="text-center">Cant. Facturada</th>
                                            <th style="width: 12%;" class="text-center">Ya Devuelta</th>
                                            <th style="width: 12%;" class="text-center">Disponible</th>
                                            <th style="width: 12%;" class="text-center">Cant. a Devolver</th>
                                            <th style="width: 12%;" class="text-end">Precio Unit.</th>
                                        </tr>
                                    </thead>
                                    <tbody id="productosFacturaBody">
                                        <!-- Se llenará dinámicamente -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Total -->
                    <div class="row justify-content-end">
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between mb-2">
                                        <strong>Total Devolución:</strong>
                                        <strong class="text-primary" id="totalDevolucion">$0.00</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fa-solid fa-times me-2"></i>Cancelar
                </button>
                <button type="button" class="btn btn-primary" id="guardarDevolucion">
                    <i class="fa-solid fa-save me-2"></i>Guardar Devolución
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Detalle de Devolución (solo visualización) -->
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
    let detalleItems = [];
    let facturaProductos = [];
    let facturaSeleccionada = null;

    // Cargar devoluciones
    cargarDevoluciones();

    // Cargar facturas activas
    cargarFacturas();

    // Generar número de documento al abrir modal
    $('#devolucionModal').on('show.bs.modal', function (e) {
        if (!$('#devolucionId').val()) {
            generarNumeroDocumento();
            $('#fecha').val(new Date().toISOString().split('T')[0]);
        }
    });

    // Limpiar modal al cerrar
    $('#devolucionModal').on('hidden.bs.modal', function () {
        $('#devolucionForm')[0].reset();
        $('#devolucionId').val('');
        $('#accion').val('guardar');
        detalleItems = [];
        facturaProductos = [];
        facturaSeleccionada = null;
        $('#productosFacturaBody').empty();
        $('#facturaInfo').hide();
        $('#productosSection').hide();
        $('#devolucionModalLabel').html('<i class="fa-solid fa-rotate-left me-2"></i>Nueva Devolución');
    });

    // Al seleccionar una factura
    $('#facturaId').on('change', function() {
        const facturaId = $(this).val();
        if (facturaId) {
            cargarDetalleFactura(facturaId);
        } else {
            $('#facturaInfo').hide();
            $('#productosSection').hide();
            facturaProductos = [];
            detalleItems = [];
            $('#productosFacturaBody').empty();
        }
    });

    // Checkbox seleccionar todos
    $('#seleccionarTodos').on('change', function() {
        const isChecked = $(this).is(':checked');
        $('.producto-checkbox').prop('checked', isChecked);
        
        if (isChecked) {
            // Auto-llenar con cantidad disponible
            $('.cantidad-devolver').each(function() {
                const maxCant = parseFloat($(this).attr('max')) || 0;
                if (maxCant > 0) {
                    $(this).val(maxCant);
                }
            });
        } else {
            $('.cantidad-devolver').val('');
        }
    });

    // Guardar devolución
    $('#guardarDevolucion').click(function() {
        if (!$('#facturaId').val()) {
            Swal.fire('Error', 'Seleccione una factura', 'error');
            return;
        }

        if (detalleItems.length === 0) {
            Swal.fire('Error', 'Agregue al menos un producto a la devolución', 'error');
            return;
        }

        if (!$('#devolucionForm')[0].checkValidity()) {
            $('#devolucionForm')[0].reportValidity();
            return;
        }

        const formData = {
            accion: $('#accion').val(),
            id: $('#devolucionId').val(),
            numero_documento: $('#numeroDocumento').val(),
            factura_id: $('#facturaId').val(),
            fecha: $('#fecha').val(),
            detalle: detalleItems
        };

        $.ajax({
            url: 'Devolucion_ajax.php',
            type: 'POST',
            data: JSON.stringify(formData),
            contentType: 'application/json',
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Éxito',
                        text: response.message,
                        showCancelButton: true,
                        confirmButtonText: '<i class="fa-solid fa-print me-2"></i>Imprimir',
                        cancelButtonText: 'Cerrar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.open('Devolucion_imprimir.php?id=' + response.devolucion_id, '_blank');
                        }
                    });
                    $('#devolucionModal').modal('hide');
                    cargarDevoluciones();
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'Error al procesar la solicitud', 'error');
            }
        });
    });

    function cargarDevoluciones() {
        $.ajax({
            url: 'Devolucion_ajax.php',
            type: 'POST',
            data: { accion: 'listar' },
            success: function(response) {
                if (response.success) {
                    let html = '';
                    response.data.forEach(function(devolucion) {
                        const estadoBadge = devolucion.activo == 1
                            ? '<span class="badge bg-success">Activo</span>'
                            : '<span class="badge bg-danger">Anulado</span>';
                        
                        html += `<tr>
                            <td>${devolucion.numero_documento}</td>
                            <td>${devolucion.factura_numero}</td>
                            <td>${devolucion.cliente_nombre}</td>
                            <td>${devolucion.fecha}</td>
                            <td>$${parseFloat(devolucion.total).toFixed(2)}</td>
                            <td>${estadoBadge}</td>
                            <td>
                                <button class="btn btn-sm btn-info" onclick="verDevolucion(${devolucion.id_devoluciones})" title="Ver">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-primary" onclick="imprimirDevolucion(${devolucion.id_devoluciones})" title="Imprimir">
                                    <i class="fa-solid fa-print"></i>
                                </button>
                                ${devolucion.activo == 1 ? `
                                <button class="btn btn-sm btn-danger" onclick="anularDevolucion(${devolucion.id_devoluciones})" title="Anular">
                                    <i class="fa-solid fa-ban"></i>
                                </button>
                                ` : ''}
                            </td>
                        </tr>`;
                    });
                    $('#devolucionTable tbody').html(html || '<tr><td colspan="7" class="text-center">No hay devoluciones registradas</td></tr>');
                }
            }
        });
    }

    function cargarFacturas() {
        $.ajax({
            url: 'Devolucion_ajax.php',
            type: 'POST',
            data: { accion: 'listar_facturas' },
            success: function(response) {
                if (response.success) {
                    let options = '<option value="">Seleccione una factura...</option>';
                    response.data.forEach(function(factura) {
                        options += `<option value="${factura.id_facturas}">${factura.numero_documento} - ${factura.cliente_nombre} - $${parseFloat(factura.total).toFixed(2)}</option>`;
                    });
                    $('#facturaId').html(options);
                }
            }
        });
    }

    function cargarDetalleFactura(facturaId) {
        $.ajax({
            url: 'Devolucion_ajax.php',
            type: 'POST',
            data: { accion: 'obtener_factura', id: facturaId },
            success: function(response) {
                if (response.success) {
                    const factura = response.data;
                    facturaSeleccionada = factura;
                    
                    // Mostrar información de la factura
                    $('#info-cliente').text(factura.cliente_nombre);
                    $('#info-fecha').text(factura.fecha);
                    $('#info-total').text('$' + parseFloat(factura.total).toFixed(2));
                    $('#facturaInfo').show();

                    // Cargar productos de la factura
                    facturaProductos = factura.detalle || [];
                    
                    if (facturaProductos.length === 0) {
                        Swal.fire('Información', 'Esta factura no tiene productos disponibles para devolver o ya fueron devueltos en su totalidad.', 'info');
                        $('#productosSection').hide();
                        $('#productosFacturaBody').empty();
                        return;
                    }
                    
                    // Construir tabla con todos los productos
                    let htmlRows = '';
                    facturaProductos.forEach(function(producto) {
                        const cantDisponible = parseFloat(producto.cantidad_disponible || producto.cantidad);
                        const cantDevuelta = parseFloat(producto.cantidad_devuelta || 0);
                        const cantOriginal = parseFloat(producto.cantidad);
                        const precio = parseFloat(producto.precio_unitario);
                        
                        if (cantDisponible > 0) {
                            htmlRows += `<tr data-producto-id="${producto.producto_id}" data-precio="${precio}">
                                <td class="text-center">
                                    <input type="checkbox" class="form-check-input producto-checkbox">
                                </td>
                                <td>${producto.producto_nombre}</td>
                                <td class="text-center">${cantOriginal.toFixed(2)}</td>
                                <td class="text-center text-danger">${cantDevuelta.toFixed(2)}</td>
                                <td class="text-center text-success"><strong>${cantDisponible.toFixed(2)}</strong></td>
                                <td>
                                    <input type="number" class="form-control form-control-sm cantidad-devolver" 
                                        min="0" max="${cantDisponible}" step="0.01" value="" 
                                        style="width: 100px;">
                                </td>
                                <td class="text-end">$${precio.toFixed(2)}</td>
                            </tr>`;
                        }
                    });
                    
                    $('#productosFacturaBody').html(htmlRows);
                    $('#productosSection').show();
                    
                    // Agregar eventos a las filas
                    agregarEventosTablaProductos();
                }
            }
        });
    }

    function agregarEventosTablaProductos() {
        // Evento cuando cambia un checkbox
        $('.producto-checkbox').on('change', function() {
            const row = $(this).closest('tr');
            const input = row.find('.cantidad-devolver');
            const maxCant = parseFloat(input.attr('max'));
            
            if ($(this).is(':checked')) {
                input.val(maxCant);
                row.addClass('table-primary');
            } else {
                input.val('');
                row.removeClass('table-primary');
            }
            calcularTotal();
        });

        // Evento cuando cambia la cantidad
        $('.cantidad-devolver').on('input', function() {
            const row = $(this).closest('tr');
            const checkbox = row.find('.producto-checkbox');
            const cantidad = parseFloat($(this).val()) || 0;
            const maxCant = parseFloat($(this).attr('max'));
            
            // Validar que no exceda el máximo
            if (cantidad > maxCant) {
                $(this).val(maxCant);
                Swal.fire('Advertencia', 'La cantidad no puede superar lo disponible', 'warning');
            }
            
            // Marcar checkbox si hay cantidad
            if (cantidad > 0) {
                checkbox.prop('checked', true);
                row.addClass('table-primary');
            } else {
                checkbox.prop('checked', false);
                row.removeClass('table-primary');
            }
            
            calcularTotal();
        });
    }

    function calcularTotal() {
        let total = 0;
        $('#productosFacturaBody tr').each(function() {
            const checkbox = $(this).find('.producto-checkbox');
            if (checkbox.is(':checked')) {
                const cantidad = parseFloat($(this).find('.cantidad-devolver').val()) || 0;
                const precio = parseFloat($(this).data('precio')) || 0;
                total += cantidad * precio;
            }
        });
        $('#total').text('$' + total.toFixed(2));
    }

    function generarNumeroDocumento() {
        $.ajax({
            url: 'Devolucion_ajax.php',
            type: 'POST',
            data: { accion: 'generar_numero' },
            success: function(response) {
                if (response.success) {
                    $('#numeroDocumento').val(response.numero);
                }
            }
        });
    }

    // Guardar devolución
    $('#guardarDevolucion').click(function() {
        const facturaId = $('#facturaId').val();
        const observacion = $('#observacion').val();
        const numeroDocumento = $('#numeroDocumento').val();
        const fecha = $('#fecha').val();

        if (!facturaId) {
            Swal.fire('Error', 'Seleccione una factura', 'error');
            return;
        }

        if (!numeroDocumento) {
            Swal.fire('Error', 'El número de documento es requerido', 'error');
            return;
        }

        if (!fecha) {
            Swal.fire('Error', 'La fecha es requerida', 'error');
            return;
        }

        // Construir detalleItems desde la tabla
        detalleItems = [];
        $('#productosFacturaBody tr').each(function() {
            const checkbox = $(this).find('.producto-checkbox');
            if (checkbox.is(':checked')) {
                const cantidad = parseFloat($(this).find('.cantidad-devolver').val()) || 0;
                if (cantidad > 0) {
                    const precio = parseFloat($(this).data('precio'));
                    detalleItems.push({
                        producto_id: $(this).data('productoId'),
                        nombre: $(this).find('td:eq(1)').text(),
                        cantidad: cantidad,
                        precio_unitario: precio,
                        subtotal: cantidad * precio
                    });
                }
            }
        });

        if (detalleItems.length === 0) {
            Swal.fire('Error', 'Debe seleccionar al menos un producto con cantidad mayor a 0', 'error');
            return;
        }

        const data = {
            accion: 'guardar',
            numero_documento: numeroDocumento,
            factura_id: facturaId,
            fecha: fecha,
            observacion: observacion,
            detalle: detalleItems
        };

        $.ajax({
            url: 'Devolucion_ajax.php',
            type: 'POST',
            data: JSON.stringify(data),
            contentType: 'application/json',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Swal.fire('Éxito', 'Devolución guardada correctamente', 'success');
                    $('#devolucionModal').modal('hide');
                    tablaDevoluciones.ajax.reload();
                } else {
                    Swal.fire('Error', response.message || response.error || 'Error al guardar', 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error AJAX:', xhr.responseText);
                Swal.fire('Error', 'Error al procesar la solicitud: ' + error, 'error');
            }
        });
    });

    window.imprimirDevolucion = function(id) {
        window.open('Devolucion_imprimir.php?id=' + id, '_blank');
    };

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
                            cargarDevoluciones();
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
