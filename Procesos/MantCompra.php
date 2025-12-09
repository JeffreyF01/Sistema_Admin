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
                    <h4 class="page-title"><i class="fa-solid fa-truck me-2"></i>Compras</h4>
                    <p class="page-subtitle">Registrar compras de proveedores — aumenta inventario y genera CxP</p>
                </div>
                <div class="col-auto">
                    <div class="user-info">
                        <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['usuario'],0,1)); ?></div>
                        <div class="user-details">
                            <div class="username"><?php echo $_SESSION['usuario']; ?></div>
                            <div class="role">Usuario</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="content-area">
        <div class="container-fluid">
            <div class="mb-3 d-flex justify-content-end">
                <button id="btnNuevaCompra" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#compraModal">
                    <i class="fa-solid fa-plus me-2"></i>Nueva Compra
                </button>
                <a href="Compra_listar.php" class="btn btn-secondary ms-2"><i class="fa-solid fa-list me-2"></i>Ver Compras</a>
            </div>

            <div class="card card-custom fade-in">
                <div class="card-header card-header-custom">
                    <h5 class="mb-0"><i class="fa-solid fa-clipboard-list me-2"></i>Compras Recientes</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-mantenimiento" id="compraTable">
                            <thead>
                                <tr>
                                    <th>N° Compra</th>
                                    <th>Proveedor</th>
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

<!-- Modal Compra -->
<div class="modal fade" id="compraModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa-solid fa-file-circle-plus me-2"></i> Registrar Compra</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="compraForm">
                    <input type="hidden" id="id_compra" name="id_compra">
                    <input type="hidden" id="accion" name="accion" value="guardar">

                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <label class="form-label">N° Documento</label>
                            <input type="text" id="numero_documento" name="numero_documento" class="form-control" readonly required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Fecha</label>
                            <input type="date" id="fecha" name="fecha" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Proveedor</label>
                            <select id="proveedor_id" name="proveedor_id" class="form-select" required>
                                <option value="">Seleccione...</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Condición de Pago</label>
                            <select id="condicion_id" name="condicion_id" class="form-select">
                                <option value="">Seleccione...</option>
                            </select>
                        </div>
                    </div>

                    <div class="card mb-3">
                        <div class="card-header bg-light"><h6 class="mb-0"><i class="fa-solid fa-boxes-stacked me-2"></i>Items</h6></div>
                        <div class="card-body">
                            <div class="row g-3 mb-3">
                                <div class="col-md-5">
                                    <label>Producto</label>
                                    <select id="producto_id" class="form-select">
                                        <option value="">Seleccione un producto...</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label>Cantidad</label>
                                    <input type="number" id="cantidad" class="form-control" 
                                           min="0.01" step="0.01" value="1" placeholder="1.00">
                                </div>
                                <div class="col-md-2">
                                    <label>Costo unit.</label>
                                    <input type="number" id="costo_unitario" class="form-control" 
                                           min="0" step="0.01" placeholder="0.00" readonly>
                                </div>
                                <div class="col-md-2">
                                    <label>Subtotal</label>
                                    <input type="text" id="subtotalItem" class="form-control" readonly>
                                </div>
                                <div class="col-md-1 d-flex align-items-end">
                                    <button type="button" id="btnAddItem" class="btn btn-success w-100"><i class="fa-solid fa-plus"></i></button>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-sm table-bordered" id="detalleCompraTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Producto</th>
                                            <th>Cantidad</th>
                                            <th>Costo Unit.</th>
                                            <th>Subtotal</th>
                                            <th>Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="row justify-content-end">
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-body d-flex justify-content-between">
                                    <strong>Total:</strong>
                                    <strong id="totalCompra" class="text-primary">$0.00</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                </form>
            </div>
            <div class="modal-footer">
                <button id="btnCancelarCompra" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fa-solid fa-times me-2"></i>Cancelar</button>
                <button id="btnSaveCompra" class="btn btn-primary"><i class="fa-solid fa-save me-2"></i>Registrar Compra</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(function(){

    let detalle = [];
    let productos = [];

    // ================================
    // CARGA INICIAL
    // ================================
    cargarCompras();
    cargarProveedores();
    cargarCondiciones();
    cargarProductos();

    // AL ABRIR EL MODAL
    $('#compraModal').on('shown.bs.modal', function () {
        if ($("#accion").val() === "guardar") {
            $('#compraForm')[0].reset();

            setTimeout(() => {
                $('#fecha').val(new Date().toISOString().split('T')[0]);
            }, 50);

            detalle = [];
            actualizarDetalle();
            generarNumero();
        }
    });

    // ================================
    // CARGA DE LISTAS
    // ================================
    function cargarCompras(){
        $.post('Compra_ajax.php', { accion: 'listar' }, function(res){
            let html = '';
            if(res.success){
                res.data.forEach(function(c){
                    let estado = c.activo == 1 ? '<span class="badge bg-success">ACTIVA</span>' : '<span class="badge bg-secondary">ANULADA</span>';
                    let acciones = `<button class="btn btn-sm btn-primary" onclick="imprimirCompra(${c.id_compras})" title="Imprimir"><i class="fa-solid fa-print"></i></button>`;
                    html += `<tr>
                        <td>${c.numero_documento}</td>
                        <td>${c.proveedor_nombre}</td>
                        <td>${c.fecha}</td>
                        <td>$${parseFloat(c.total).toFixed(2)}</td>
                        <td>${estado}</td>
                        <td>${acciones}</td>
                    </tr>`;
                });
            }
            $('#compraTable tbody').html(html || '<tr><td colspan="6" class="text-center">No hay compras</td></tr>');
        }, 'json');
    }

    function cargarProveedores(){
        $.post('Compra_ajax.php',{ accion:'listar_proveedores' }, function(res){
            if(res.success){
                let opts = '<option value="">Seleccione...</option>';
                res.data.forEach(function(p){ opts += `<option value="${p.id_proveedores}">${p.nombre}</option>`; });
                $('#proveedor_id').html(opts);
            }
        }, 'json');
    }

    function cargarCondiciones(){
        $.post('Compra_ajax.php',{ accion:'listar_condiciones' }, function(res){
            if(res.success){
                let opts = '<option value="">Seleccione...</option>';
                res.data.forEach(function(c){ opts += `<option value="${c.id_condiciones_pago}" data-dias="${c.dias_plazo}">${c.nombre} (${c.dias_plazo} días)</option>`; });
                $('#condicion_id').html(opts);
            }
        }, 'json');
    }

    function cargarProductos(){
        $.post('Compra_ajax.php',{ accion:'listar_productos' }, function(res){
            if(res.success){
                productos = res.data;
                let opts = '<option value="">Seleccione un producto...</option>';
                res.data.forEach(function(p){
                    opts += `<option value="${p.id_productos}" data-costo="${p.costo || 0}">${p.nombre} - Stock: ${parseFloat(p.stock||0)}</option>`;
                });
                $('#producto_id').html(opts);
            }
        }, 'json');
    }

    function generarNumero(){
        $.post('Compra_ajax.php',{ accion:'generar_numero' }, function(res){
            if(res.success) $('#numero_documento').val(res.numero);
        }, 'json');
    }

    // ================================
    // ITEMS
    // ================================
    $('#producto_id').on('change', function(){
        const opt = $(this).find('option:selected');
        const costo = parseFloat(opt.data('costo')) || 0;
        $('#costo_unitario').val(costo.toFixed(2));
        calcularSubItem();
    });

    $('#cantidad').on('input change', calcularSubItem);

    function calcularSubItem(){
        const cant = parseFloat($('#cantidad').val()) || 0;
        const costo = parseFloat($('#costo_unitario').val()) || 0;
        $('#subtotalItem').val('$' + (cant * costo).toFixed(2));
    }

    $('#btnAddItem').click(function(){
        const prodId = $('#producto_id').val();
        const producto = productos.find(p => p.id_productos == prodId);
        const cantidad = parseFloat($('#cantidad').val()) || 0;
        const costo = parseFloat($('#costo_unitario').val()) || 0;

        if(!prodId){ Swal.fire('Error','Seleccione producto','error'); return; }
        if(cantidad <= 0){ Swal.fire('Error','Cantidad invalida','error'); return; }

        const existente = detalle.find(i => i.producto_id == prodId);
        if(existente){
            existente.cantidad += cantidad;
            existente.costo_unitario = costo;
            existente.subtotal = existente.cantidad * existente.costo_unitario;
        } else {
            detalle.push({
                producto_id: prodId,
                nombre: producto.nombre,
                cantidad: cantidad,
                costo_unitario: costo,
                subtotal: cantidad * costo
            });
        }

        // reset
        $('#producto_id').val('');
        $('#cantidad').val('1');
        $('#costo_unitario').val('');
        $('#subtotalItem').val('');

        actualizarDetalle();
    });

    function actualizarDetalle(){
        let html = '';
        let total = 0;

        detalle.forEach((it, idx) => {
            total += parseFloat(it.subtotal);
            html += `<tr>
                <td>${it.nombre}</td>
                <td>${it.cantidad}</td>
                <td>$${parseFloat(it.costo_unitario).toFixed(2)}</td>
                <td>$${parseFloat(it.subtotal).toFixed(2)}</td>
                <td><button class="btn btn-sm btn-danger" onclick="removeItemCompra(${idx})"><i class="fa-solid fa-trash"></i></button></td>
            </tr>`;
        });

        $('#detalleCompraTable tbody').html(html || '<tr><td colspan="5" class="text-center">No hay items</td></tr>');
        $('#totalCompra').text('$' + total.toFixed(2));
    }

    window.removeItemCompra = function(i){
        detalle.splice(i,1);
        actualizarDetalle();
    };

    // ================================
    // GUARDAR COMPRA
    // ================================
    $('#btnSaveCompra').click(function(){
        if(detalle.length === 0){ Swal.fire('Error','Agregue al menos un item','error'); return; }
        if(!$('#proveedor_id').val()){ Swal.fire('Error','Seleccione proveedor','error'); return; }
        if(!$('#fecha').val()){ Swal.fire('Error','Seleccione fecha','error'); return; }
        
        // Validar cantidades positivas
        let errorCantidad = false;
        detalle.forEach(function(item){
            if(!Validaciones.validarPositivo(item.cantidad)){
                Swal.fire('Error', 'Todas las cantidades deben ser mayores a 0', 'error');
                errorCantidad = true;
                return false;
            }
        });
        if(errorCantidad) return;

        const payload = {
            accion: $('#accion').val(),
            id: $('#id_compra').val(),
            numero_documento: $('#numero_documento').val(),
            fecha: $('#fecha').val(),
            proveedor_id: $('#proveedor_id').val(),
            condicion_id: $('#condicion_id').val(),
            detalle: detalle
        };

        $.ajax({
            url: 'Compra_ajax.php',
            method: 'POST',
            data: JSON.stringify(payload),
            contentType: 'application/json',
            success: function(res){
                if(res.success){
                    Swal.fire('Éxito', res.message, 'success');
                    $('#compraModal').modal('hide');
                    cargarCompras();
                } else Swal.fire('Error', res.message, 'error');
            },
            error: function(){ Swal.fire('Error','Error procesando la solicitud','error'); }
        });
    });

    // ================================
    // IMPRIMIR
    // ================================
    window.imprimirCompra = function(id){
        window.open('Compra_imprimir.php?id=' + id, '_blank');
    };

}); // ← AQUI SE CIERRA TODO
</script>


<?php include '../includes/footer.php'; ?>
