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
            <div class="mb-3">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#facturaModal">
                    <i class="fa-solid fa-plus me-2"></i>Nueva Factura
                </button>
                <a href="Factura_listar.php" class="btn btn-secondary">
                    <i class="fa-solid fa-list me-2"></i>Ver Todas las Facturas
                </a>
            </div>

            <!-- Tabla de Facturas -->
            <div class="card card-custom fade-in">
                <div class="card-header card-header-custom">
                    <h5 class="mb-0"><i class="fa-solid fa-file-invoice me-2"></i>Facturas Recientes</h5>
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
                            <h6 class="mb-0"><i class="fa-solid fa-box me-2"></i>Productos</h6>
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

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    let detalleItems = [];
    let productosList = [];

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
    });

    // Limpiar modal al cerrar
    $('#facturaModal').on('hidden.bs.modal', function () {
        $('#facturaForm')[0].reset();
        $('#facturaId').val('');
        $('#accion').val('guardar');
        detalleItems = [];
        actualizarTablaDetalle();
        $('#facturaModalLabel').html('<i class="fa-solid fa-file-invoice-dollar me-2"></i>Nueva Factura');
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
            detalle: detalleItems
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
        window.location.href = 'Factura_listar.php?id=' + id;
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
});
</script>
<?php include '../includes/footer.php'; ?>
