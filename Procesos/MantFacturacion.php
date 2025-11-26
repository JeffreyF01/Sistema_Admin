<?php
session_start();
if(!isset($_SESSION['usuario'])) {
    header("Location: ../index.html");
    exit();
}
require_once '../conexion.php';
include '../includes/header.php';
include '../includes/sidebar.php';
// Obtener cotización origen si se pasó como parámetro
$cotizacion_id = isset($_GET['cotizacion_id']) ? intval($_GET['cotizacion_id']) : 0;
?>
<div class="main-content">
    <div class="main-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col">
                    <h4 class="page-title">
                        <i class="fa-solid fa-file-invoice-dollar me-2"></i>Facturación
                    </h4>
                    <p class="page-subtitle">Crea y gestiona facturas de venta</p>
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
            <!-- Botón para nueva factura -->
            <div class="row mb-3">
                <div class="col-12 d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#facturaModal">
                        <i class="fa-solid fa-plus me-2"></i>Nueva Factura
                    </button>
                    <a href="../Consultas/Consulta_factura.php" class="btn btn-secondary">
                        <i class="fa-solid fa-magnifying-glass me-2"></i>Ir a Consulta de Facturas
                    </a>
                </div>
            </div>

            <!-- Tabla de Facturas -->
            <div class="card card-custom fade-in">
                <div class="card-header card-header-custom">
                    <h5 class="mb-0"><i class="fa-solid fa-file-invoice-dollar me-2"></i>Facturas Recientes</h5>
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

<!-- Modal para Nueva/Editar Factura -->
<div class="modal fade" id="facturaModal" tabindex="-1" aria-labelledby="facturaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="facturaModalLabel">
                    <i class="fa-solid fa-file-invoice-dollar me-2"></i>Nueva Factura
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="facturaForm">
                    <input type="hidden" id="facturaId" name="id">
                    <input type="hidden" id="accion" name="accion" value="guardar">
                    <input type="hidden" id="cotizacionOrigenId" name="cotizacion_id" value="">

                    <!-- Información de la Factura -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <label for="numeroDocumento" class="form-label text-required">N° Documento</label>
                            <input type="text" class="form-control form-control-custom" id="numeroDocumento" name="numero_documento" required readonly>
                        </div>
                        <div class="col-md-3">
                            <label for="fecha" class="form-label text-required">Fecha</label>
                            <input type="date" class="form-control form-control-custom" id="fecha" name="fecha" required>
                        </div>
                        <div class="col-md-3">
                            <label for="clienteId" class="form-label text-required">Cliente</label>
                            <select class="form-select form-control-custom" id="clienteId" name="cliente_id" required>
                                <option value="">Seleccione...</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="condicionId" class="form-label text-required">Condición de Pago</label>
                            <select class="form-select form-control-custom" id="condicionId" name="condicion_id" required>
                                <option value="">Seleccione...</option>
                            </select>
                        </div>
                    </div>

                    <!-- Sección para agregar productos -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fa-solid fa-boxes-stacked me-2"></i>Productos</h6>
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
                                    <label for="cantidad" class="form-label">Cantidad</label>
                                    <input type="number" class="form-control form-control-custom" id="cantidad" min="1" step="1" value="1">
                                </div>
                                <div class="col-md-2">
                                    <label for="precio" class="form-label">Precio Unit.</label>
                                    <input type="number" class="form-control form-control-custom" id="precio" min="0" step="0.01" readonly>
                                </div>
                                <div class="col-md-2">
                                    <label for="subtotalItem" class="form-label">Subtotal</label>
                                    <input type="text" class="form-control form-control-custom" id="subtotalItem" readonly>
                                </div>
                                <div class="col-md-1 d-flex align-items-end">
                                    <button type="button" class="btn btn-success w-100" id="agregarProducto">
                                        <i class="fa-solid fa-plus"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Tabla de productos agregados -->
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered" id="detalleTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 35%;">Producto</th>
                                            <th style="width: 12%;">Cantidad</th>
                                            <th style="width: 13%;">Precio Unit.</th>
                                            <th style="width: 15%;">Subtotal</th>
                                            <th style="width: 25%;">Acciones</th>
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
                                        <strong>Total:</strong>
                                        <strong class="text-primary" id="totalFactura">$0.00</strong>
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
                <button type="button" class="btn btn-primary" id="guardarFactura">
                    <i class="fa-solid fa-save me-2"></i>Guardar Factura
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Detalle de Factura (solo visualización) -->
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
                                <th style="width: 12%;" class="text-end">Cant. Facturada</th>
                                <th style="width: 12%;" class="text-end">Cant. Devuelta</th>
                                <th style="width: 12%;" class="text-end">Cant. Neta</th>
                                <th style="width: 15%;" class="text-end">Precio Unit.</th>
                                <th style="width: 15%;" class="text-end">Subtotal</th>
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
    </div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    let detalleItems = [];
    let productosList = [];
    const cotizacionOrigen = <?php echo $cotizacion_id; ?>;
    let cotizacionCargada = false;

    // Cargar facturas
    cargarFacturas();

    // Cargar clientes
    cargarClientes();

    // Cargar condiciones de pago
    cargarCondicionesPago();

    // Cargar productos
    cargarProductos();

    // Generar número de documento al abrir modal
    $('#facturaModal').on('show.bs.modal', function (e) {
        if (!$('#facturaId').val()) {
            generarNumeroDocumento();
            $('#fecha').val(new Date().toISOString().split('T')[0]);
        }
        // Asegurar que los productos y su stock estén actualizados al abrir
        cargarProductos();
        if(cotizacionOrigen>0){
            $('#cotizacionOrigenId').val(cotizacionOrigen);
        }
    });

    // Limpiar modal al cerrar
    $('#facturaModal').on('hidden.bs.modal', function () {
        $('#facturaForm')[0].reset();
        $('#facturaId').val('');
        $('#accion').val('guardar');
        detalleItems = [];
        actualizarTablaDetalle();
        $('#facturaModalLabel').html('<i class="fa-solid fa-file-invoice-dollar me-2"></i>Nueva Factura');
        // Rehabilitar selección de cliente al salir de flujo de cotización
        $('#clienteId').prop('disabled', false).removeClass('bg-light');
        $('#clienteId').attr('title','');
    });

    // Calcular subtotal al cambiar cantidad o producto
    $('#productoId, #cantidad').on('change', function() {
        calcularSubtotalItem();
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

        const producto = productosList.find(p => p.id_productos == productoId);
        
        if (!producto) {
            Swal.fire('Error', 'Producto no encontrado', 'error');
            return;
        }

        // Verificar stock disponible
        const stockDisponible = parseFloat(producto.stock) || 0;
        if (cantidad > stockDisponible) {
            Swal.fire('Error', `Stock insuficiente. Disponible: ${stockDisponible}`, 'error');
            return;
        }

        // Verificar si el producto ya está en el detalle
        const existente = detalleItems.find(item => item.producto_id == productoId);
        if (existente) {
            const nuevaCantidad = existente.cantidad + cantidad;
            if (nuevaCantidad > stockDisponible) {
                Swal.fire('Error', `Stock insuficiente. Disponible: ${stockDisponible}`, 'error');
                return;
            }
            existente.cantidad = nuevaCantidad;
            existente.subtotal = existente.cantidad * existente.precio_unitario;
        } else {
            detalleItems.push({
                producto_id: productoId,
                nombre: producto.nombre,
                cantidad: cantidad,
                precio_unitario: precio,
                subtotal: cantidad * precio
            });
        }

        // Limpiar campos
        $('#productoId').val('');
        $('#cantidad').val('1');
        $('#precio').val('');
        $('#subtotalItem').val('');

        actualizarTablaDetalle();
    });

    // Guardar factura
    $('#guardarFactura').click(function() {
        if (detalleItems.length === 0) {
            Swal.fire('Error', 'Agregue al menos un producto a la factura', 'error');
            return;
        }

        if (!$('#facturaForm')[0].checkValidity()) {
            $('#facturaForm')[0].reportValidity();
            return;
        }

        const formData = {
            accion: $('#accion').val(),
            id: $('#facturaId').val(),
            numero_documento: $('#numeroDocumento').val(),
            cliente_id: $('#clienteId').val(),
            fecha: $('#fecha').val(),
            condicion_id: $('#condicionId').val(),
            detalle: detalleItems,
            cotizacion_id: $('#cotizacionOrigenId').val() || null
        };

        $.ajax({
            url: 'Factura_ajax.php',
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
                            window.open('Factura_imprimir.php?id=' + response.factura_id, '_blank');
                        }
                    });
                    $('#facturaModal').modal('hide');
                    cargarFacturas();
                    // Refrescar catálogo de productos para que el stock refleje la última factura
                    cargarProductos();
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'Error al procesar la solicitud', 'error');
            }
        });
    });

    function cargarFacturas() {
        $.ajax({
            url: 'Factura_ajax.php',
            type: 'POST',
            data: { accion: 'listar' },
            success: function(response) {
                if (response.success) {
                    let html = '';
                    response.data.forEach(function(factura) {
                        const estadoBadge = factura.activo == 1
                            ? '<span class="badge bg-success">Activo</span>'
                            : '<span class="badge bg-danger">Anulado</span>';
                        
                        html += `<tr>
                            <td>${factura.numero_documento}</td>
                            <td>${factura.cliente_nombre}</td>
                            <td>${factura.fecha}</td>
                            <td>${factura.condicion_nombre}</td>
                            <td>$${parseFloat(factura.total).toFixed(2)}</td>
                            <td>${estadoBadge}</td>
                            <td>
                                <button class="btn btn-sm btn-info" onclick="verFactura(${factura.id_facturas})" title="Ver">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-primary" onclick="imprimirFactura(${factura.id_facturas})" title="Imprimir">
                                    <i class="fa-solid fa-print"></i>
                                </button>
                                ${factura.activo == 1 ? `
                                <button class="btn btn-sm btn-danger" onclick="anularFactura(${factura.id_facturas})" title="Anular">
                                    <i class="fa-solid fa-ban"></i>
                                </button>
                                ` : ''}
                            </td>
                        </tr>`;
                    });
                    $('#facturaTable tbody').html(html || '<tr><td colspan="7" class="text-center">No hay facturas registradas</td></tr>');
                }
            }
        });
    }

    function cargarClientes() {
        $.ajax({
            url: 'Factura_ajax.php',
            type: 'POST',
            data: { accion: 'listar_clientes' },
            success: function(response) {
                if (response.success) {
                    let options = '<option value="">Seleccione...</option>';
                    response.data.forEach(function(cliente) {
                        options += `<option value="${cliente.id_clientes}">${cliente.nombre}</option>`;
                    });
                    $('#clienteId').html(options);
                }
            }
        });
    }

    function cargarCondicionesPago() {
        $.ajax({
            url: 'Factura_ajax.php',
            type: 'POST',
            data: { accion: 'listar_condiciones' },
            success: function(response) {
                if (response.success) {
                    let options = '<option value="">Seleccione...</option>';
                    response.data.forEach(function(condicion) {
                        options += `<option value="${condicion.id_condiciones_pago}">${condicion.nombre} (${condicion.dias_plazo} días)</option>`;
                    });
                    $('#condicionId').html(options);
                }
            }
        });
    }

    function cargarProductos() {
        $.ajax({
            url: 'Factura_ajax.php',
            type: 'POST',
            data: { accion: 'listar_productos' },
            success: function(response) {
                if (response.success) {
                    productosList = response.data;
                    let options = '<option value="">Seleccione un producto...</option>';
                    response.data.forEach(function(producto) {
                        const stock = parseFloat(producto.stock) || 0;
                        const precio = parseFloat(producto.precio_venta) || 0;
                        options += `<option value="${producto.id_productos}" data-precio="${precio}" data-stock="${stock}">
                            ${producto.nombre} - Stock: ${stock} - $${precio.toFixed(2)}
                        </option>`;
                    });
                    $('#productoId').html(options);

                    // Evento cuando se selecciona un producto
                    $('#productoId').off('change').on('change', function() {
                        const selectedOption = $(this).find('option:selected');
                        const precio = selectedOption.data('precio') || 0;
                        $('#precio').val(parseFloat(precio).toFixed(2));
                        calcularSubtotalItem();
                    });

                    // Si se vino desde una cotización, cargarla una vez que productos estén listos
                    if (cotizacionOrigen > 0 && !cotizacionCargada) {
                        cargarCotizacion(cotizacionOrigen);
                    }
                }
            }
        });
    }

    function generarNumeroDocumento() {
        $.ajax({
            url: 'Factura_ajax.php',
            type: 'POST',
            data: { accion: 'generar_numero' },
            success: function(response) {
                if (response.success) {
                    $('#numeroDocumento').val(response.numero);
                }
            }
        });
    }

    function calcularSubtotalItem() {
        const cantidad = parseFloat($('#cantidad').val()) || 0;
        const precio = parseFloat($('#precio').val()) || 0;
        const subtotal = cantidad * precio;
        $('#subtotalItem').val('$' + subtotal.toFixed(2));
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
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-outline-primary" onclick="decrementarItem(${index})" title="Disminuir cantidad">
                            <i class="fa-solid fa-minus"></i>
                        </button>
                        <button type="button" class="btn btn-outline-success" onclick="incrementarItem(${index})" title="Aumentar cantidad">
                            <i class="fa-solid fa-plus"></i>
                        </button>
                        <button type="button" class="btn btn-outline-danger" onclick="eliminarItem(${index})" title="Eliminar producto">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>`;
        });

        $('#detalleTable tbody').html(html || '<tr><td colspan="5" class="text-center">No hay productos agregados</td></tr>');
        $('#totalFactura').text('$' + total.toFixed(2));
    }

    window.incrementarItem = function(index) {
        const item = detalleItems[index];
        const producto = productosList.find(p => p.id_productos == item.producto_id);
        
        if (!producto) {
            Swal.fire('Error', 'Producto no encontrado', 'error');
            return;
        }

        const nuevaCantidad = item.cantidad + 1;
        const stockDisponible = parseFloat(producto.stock) || 0;
        
        if (nuevaCantidad > stockDisponible) {
            Swal.fire('Error', `Stock insuficiente. Disponible: ${stockDisponible}`, 'warning');
            return;
        }

        item.cantidad = nuevaCantidad;
        item.subtotal = item.cantidad * item.precio_unitario;
        actualizarTablaDetalle();
    };

    window.decrementarItem = function(index) {
        const item = detalleItems[index];
        
        if (item.cantidad <= 1) {
            Swal.fire({
                title: '¿Eliminar producto?',
                text: 'La cantidad mínima es 1. ¿Desea eliminar el producto del detalle?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    eliminarItem(index);
                }
            });
            return;
        }

        item.cantidad -= 1;
        item.subtotal = item.cantidad * item.precio_unitario;
        actualizarTablaDetalle();
    };

    window.eliminarItem = function(index) {
        detalleItems.splice(index, 1);
        actualizarTablaDetalle();
    };

    window.imprimirFactura = function(id) {
        window.open('Factura_imprimir.php?id=' + id, '_blank');
    };

    window.verFactura = function(id) {
        $.ajax({
            url: 'Factura_ajax.php',
            type: 'POST',
            data: { accion: 'obtener', id: id },
            success: function(response) {
                if (!response.success) {
                    Swal.fire('Error', response.message || 'No se pudo cargar la factura', 'error');
                    return;
                }
                const f = response.data;
                $('#df-numero').text(f.numero_documento);
                $('#df-fecha').text(new Date(f.fecha).toLocaleDateString());
                $('#df-usuario').text(f.usuario_nombre || '');
                $('#df-cliente').text(`${f.cliente_nombre} ${f.doc_identidad ? '('+f.doc_identidad+')' : ''}`);
                $('#df-condicion').text(`${f.condicion_nombre} ${f.dias_plazo ? '('+f.dias_plazo+' días)' : ''}`);

                const $tbody = $('#df-detalle');
                $tbody.empty();
                let total = 0;
                let totalDevuelto = 0;
                let hayDevoluciones = false;
                
                (f.detalle || []).forEach(function(it){
                    const cantidad = parseFloat(it.cantidad) || 0;
                    const cantidadDevuelta = parseFloat(it.cantidad_devuelta) || 0;
                    const cantidadNeta = parseFloat(it.cantidad_neta) || 0;
                    const precio = parseFloat(it.precio_unitario) || 0;
                    const subtotalFacturado = cantidad * precio;
                    const subtotalDevuelto = cantidadDevuelta * precio;
                    const subtotalNeto = cantidadNeta * precio;
                    
                    total += subtotalFacturado;
                    totalDevuelto += subtotalDevuelto;
                    
                    if (cantidadDevuelta > 0) {
                        hayDevoluciones = true;
                    }
                    
                    const rowClass = cantidadDevuelta > 0 ? 'table-warning' : '';
                    const devueltaBadge = cantidadDevuelta > 0 ? '<i class="fa-solid fa-rotate-left text-danger" title="Tiene devoluciones"></i> ' : '';
                    
                    $tbody.append(`
                        <tr class="${rowClass}">
                            <td>${devueltaBadge}${it.producto_nombre || ''}</td>
                            <td class="text-end">${cantidad.toLocaleString(undefined,{maximumFractionDigits:2})}</td>
                            <td class="text-end ${cantidadDevuelta > 0 ? 'text-danger fw-bold' : ''}">${cantidadDevuelta.toLocaleString(undefined,{maximumFractionDigits:2})}</td>
                            <td class="text-end ${cantidadDevuelta > 0 ? 'fw-bold' : ''}">${cantidadNeta.toLocaleString(undefined,{maximumFractionDigits:2})}</td>
                            <td class="text-end">$${precio.toFixed(2)}</td>
                            <td class="text-end">$${subtotalNeto.toFixed(2)}</td>
                        </tr>
                    `);
                });
                
                // Agregar filas de totales y actualizar total visible superior
                let totalNeto = total - totalDevuelto;
                if (hayDevoluciones) {
                    $tbody.append(`
                        <tr class="table-light fw-bold">
                            <td colspan="5" class="text-end">Subtotal Facturado:</td>
                            <td class="text-end">$${total.toFixed(2)}</td>
                        </tr>
                        <tr class="table-danger">
                            <td colspan="5" class="text-end text-danger">(-) Total Devuelto:</td>
                            <td class="text-end text-danger">$${totalDevuelto.toFixed(2)}</td>
                        </tr>
                        <tr class="table-success fw-bold">
                            <td colspan="5" class="text-end">Total Neto:</td>
                            <td class="text-end text-success">$${totalNeto.toFixed(2)}</td>
                        </tr>
                    `);

                    // Mostrar el total neto arriba, con tooltip explicativo
                    $('#df-total')
                        .text(`$${totalNeto.toFixed(2)}`)
                        .attr('title', `Facturado: $${total.toFixed(2)} | Devuelto: $${totalDevuelto.toFixed(2)} | Neto: $${totalNeto.toFixed(2)}`)
                        .addClass('text-decoration-underline')
                        .css('cursor','help');
                } else {
                    $('#df-total')
                        .text(`$${total.toFixed(2)}`)
                        .attr('title','Total facturado')
                        .removeClass('text-decoration-underline')
                        .css('cursor','default');
                }


                $('#df-imprimir').off('click').on('click', function(){
                    imprimirFactura(id);
                });

                const modal = new bootstrap.Modal(document.getElementById('detalleFacturaModal'));
                modal.show();
            },
            error: function() {
                Swal.fire('Error', 'Error al obtener el detalle de la factura', 'error');
            }
        });
    };

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
                            cargarFacturas();
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    }
                });
            }
        });
    };

    function cargarCotizacion(id) {
        $.ajax({
            url: 'Factura_ajax.php',
            type: 'POST',
            data: { accion: 'cargar_desde_cotizacion', id: id },
            success: function(response) {
                if (!response.success) {
                    Swal.fire('Error', response.message || 'No se pudo cargar la cotización', 'error');
                    return;
                }
                const cot = response.data.cotizacion;
                const det = response.data.detalle || [];
                // Asignar cliente y fecha (usar fecha actual para factura)
                $('#clienteId').val(cot.cliente_id);
                // Bloquear cambio de cliente si viene de cotización
                $('#clienteId').prop('disabled', true).addClass('bg-light');
                $('#clienteId').attr('title','Cliente definido por la cotización');
                $('#fecha').val(new Date().toISOString().split('T')[0]);
                // Marcar etiqueta del modal
                $('#facturaModalLabel').html(`<i class="fa-solid fa-file-invoice-dollar me-2"></i>Factura desde ${cot.numero_documento}`);

                // Mapear detalle respetando stock actual
                detalleItems = det.map(function(item){
                    const productoActual = productosList.find(p => parseInt(p.id_productos) === parseInt(item.producto_id));
                    let stockDisp = productoActual ? parseFloat(productoActual.stock) : parseFloat(item.stock);
                    let cantidad = parseFloat(item.cantidad);
                    if (cantidad > stockDisp) {
                        cantidad = stockDisp; // ajustar para no exceder stock
                    }
                    const precio = parseFloat(item.precio_unitario);
                    return {
                        producto_id: item.producto_id,
                        nombre: item.nombre,
                        cantidad: cantidad,
                        precio_unitario: precio,
                        subtotal: cantidad * precio
                    };
                });
                actualizarTablaDetalle();
                cotizacionCargada = true;
                // Abrir modal de factura automáticamente
                const modal = new bootstrap.Modal(document.getElementById('facturaModal'));
                modal.show();
                Swal.fire('Cotización cargada', 'Puede agregar o quitar productos antes de guardar la factura.', 'info');
            },
            error: function(){
                Swal.fire('Error','Error al cargar la cotización','error');
            }
        });
    }
});
</script>
<?php include '../includes/footer.php'; ?>
