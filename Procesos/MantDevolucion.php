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
                    <a href="Devolucion_listar.php" class="btn btn-secondary">
                        <i class="fa-solid fa-list me-2"></i>Ver Todas las Devoluciones
                    </a>
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

                    <!-- Sección para agregar productos a devolver -->
                    <div class="card mb-3" id="productosSection" style="display:none;">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fa-solid fa-boxes-stacked me-2"></i>Productos de la Factura</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3 mb-3">
                                <div class="col-md-5">
                                    <label for="productoId" class="form-label">Producto</label>
                                    <select class="form-select form-control-custom" id="productoId">
                                        <option value="">Seleccione un producto...</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label for="cantidadFactura" class="form-label">Cant. Facturada</label>
                                    <input type="number" class="form-control form-control-custom" id="cantidadFactura" readonly>
                                </div>
                                <div class="col-md-2">
                                    <label for="cantidad" class="form-label">Cant. Devolver</label>
                                    <input type="number" class="form-control form-control-custom" id="cantidad" min="1" step="1" value="1">
                                </div>
                                <div class="col-md-2">
                                    <label for="precio" class="form-label">Precio Unit.</label>
                                    <input type="number" class="form-control form-control-custom" id="precio" readonly>
                                </div>
                                <div class="col-md-1 d-flex align-items-end">
                                    <button type="button" class="btn btn-success w-100" id="agregarProducto">
                                        <i class="fa-solid fa-plus"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Tabla de productos a devolver -->
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered" id="detalleTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 40%;">Producto</th>
                                            <th style="width: 15%;">Cantidad</th>
                                            <th style="width: 15%;">Precio Unit.</th>
                                            <th style="width: 15%;">Subtotal</th>
                                            <th style="width: 15%;">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
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
        actualizarTablaDetalle();
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
            actualizarTablaDetalle();
        }
    });

    // Al seleccionar un producto
    $('#productoId').on('change', function() {
        const productoId = $(this).val();
        if (productoId) {
            const selectedOption = $(this).find('option:selected');
            const cantidadDisponible = parseFloat(selectedOption.data('cantidad-disponible')) || 0;
            const cantidadOriginal = parseFloat(selectedOption.data('cantidad-original')) || 0;
            const cantidadDevuelta = parseFloat(selectedOption.data('cantidad-devuelta')) || 0;
            
            const producto = facturaProductos.find(p => p.producto_id == productoId);
            if (producto) {
                $('#cantidadFactura').val(cantidadOriginal.toFixed(2) + ' (Devuelto: ' + cantidadDevuelta.toFixed(2) + ')');
                $('#precio').val(parseFloat(producto.precio_unitario).toFixed(2));
                $('#cantidad').attr('max', cantidadDisponible).val(Math.min(1, cantidadDisponible));
            }
        } else {
            $('#cantidadFactura').val('');
            $('#precio').val('');
            $('#cantidad').val('1');
        }
    });

    // Agregar producto al detalle
    $('#agregarProducto').click(function() {
        const productoId = $('#productoId').val();
        const cantidad = parseFloat($('#cantidad').val()) || 0;
        const precio = parseFloat($('#precio').val()) || 0;

        if (!productoId) {
            Swal.fire('Error', 'Seleccione un producto', 'error');
            return;
        }

        if (cantidad <= 0) {
            Swal.fire('Error', 'La cantidad debe ser mayor a 0', 'error');
            return;
        }

        const selectedOption = $('#productoId').find('option:selected');
        const cantidadDisponible = parseFloat(selectedOption.data('cantidad-disponible')) || 0;
        
        const producto = facturaProductos.find(p => p.producto_id == productoId);
        
        if (!producto) {
            Swal.fire('Error', 'Producto no encontrado', 'error');
            return;
        }
        
        // Verificar si ya existe en el detalle
        const existente = detalleItems.find(item => item.producto_id == productoId);
        const cantidadYaDevuelta = existente ? existente.cantidad : 0;
        const cantidadTotal = cantidadYaDevuelta + cantidad;

        if (cantidadTotal > cantidadDisponible) {
            Swal.fire('Error', `No puede devolver más de lo disponible. Cantidad disponible: ${cantidadDisponible.toFixed(2)}`, 'error');
            return;
        }

        if (existente) {
            existente.cantidad = cantidadTotal;
            existente.subtotal = existente.cantidad * existente.precio_unitario;
        } else {
            detalleItems.push({
                producto_id: productoId,
                nombre: producto.producto_nombre,
                cantidad: cantidad,
                precio_unitario: precio,
                subtotal: cantidad * precio
            });
        }

        // Limpiar campos
        $('#productoId').val('');
        $('#cantidad').val('1');
        $('#precio').val('');
        $('#cantidadFactura').val('');

        actualizarTablaDetalle();
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
                        detalleItems = [];
                        actualizarTablaDetalle();
                        return;
                    }
                    
                    let optionsProductos = '<option value="">Seleccione un producto...</option>';
                    facturaProductos.forEach(function(producto) {
                        const cantDisponible = parseFloat(producto.cantidad_disponible || producto.cantidad);
                        const cantDevuelta = parseFloat(producto.cantidad_devuelta || 0);
                        const cantOriginal = parseFloat(producto.cantidad);
                        
                        optionsProductos += `<option value="${producto.producto_id}" 
                            data-cantidad-disponible="${cantDisponible}"
                            data-cantidad-original="${cantOriginal}"
                            data-cantidad-devuelta="${cantDevuelta}">
                            ${producto.producto_nombre} - Disponible: ${cantDisponible.toFixed(2)} (Facturado: ${cantOriginal.toFixed(2)}) - $${parseFloat(producto.precio_unitario).toFixed(2)}
                        </option>`;
                    });
                    $('#productoId').html(optionsProductos);
                    
                    $('#productosSection').show();
                    
                    // Limpiar detalle anterior
                    detalleItems = [];
                    actualizarTablaDetalle();
                }
            }
        });
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

    function actualizarTablaDetalle() {
        let html = '';
        let total = 0;

        detalleItems.forEach(function(item, index) {
            total += item.subtotal;
            const cantidad = parseFloat(item.cantidad) || 0;
            const precioUnit = parseFloat(item.precio_unitario) || 0;
            const subtotal = parseFloat(item.subtotal) || 0;
            html += `<tr>
                <td>${item.nombre}</td>
                <td>${cantidad}</td>
                <td>$${precioUnit.toFixed(2)}</td>
                <td>$${subtotal.toFixed(2)}</td>
                <td>
                    <button type="button" class="btn btn-sm btn-danger" onclick="eliminarItem(${index})" title="Eliminar">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </td>
            </tr>`;
        });

        $('#detalleTable tbody').html(html || '<tr><td colspan="5" class="text-center">No hay productos agregados</td></tr>');
        $('#totalDevolucion').text('$' + total.toFixed(2));
    }

    window.eliminarItem = function(index) {
        detalleItems.splice(index, 1);
        actualizarTablaDetalle();
    };

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
