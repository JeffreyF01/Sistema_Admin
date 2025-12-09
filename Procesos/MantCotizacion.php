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
                    <h4 class="page-title"><i class="fa-solid fa-file-signature me-2"></i>Cotizaciones</h4>
                    <p class="page-subtitle">Crear y gestionar cotizaciones</p>
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
            <div class="row mb-3">
                <div class="col-12 d-flex justify-content-end gap-2">
                    <button id="btnNuevaCot" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#cotModal">
                        <i class="fa-solid fa-plus me-2"></i>Nueva Cotización
                    </button>
                    <a href="Cotizacion_listar.php" class="btn btn-secondary">
                        <i class="fa-solid fa-list me-2"></i>Ver Cotizaciones
                    </a>
                </div>
            </div>

            <div class="card card-custom fade-in">
                <div class="card-header card-header-custom">
                    <h5 class="mb-0"><i class="fa-solid fa-file-signature me-2"></i>Cotizaciones Recientes</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-mantenimiento" id="cotTable">
                            <thead>
                                <tr>
                                    <th>N° Cotización</th>
                                    <th>Cliente</th>
                                    <th>Fecha</th>
                                    <th>Valida Hasta</th>
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

<div class="modal fade" id="cotModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa-solid fa-receipt me-2"></i> Cotización <span id="badgeEstado" class="badge bg-success ms-2">ACTIVA</span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="cotForm">
                    <input type="hidden" id="id_cotizacion" name="id_cotizacion">
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
                            <label class="form-label">Válida hasta</label>
                            <input type="date" id="valida_hasta" name="valida_hasta" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Cliente</label>
                            <select id="cliente_id" name="cliente_id" class="form-select" required>
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
                                    <input type="number" id="cantidad" class="form-control" min="1" step="1" value="1">
                                </div>
                                <div class="col-md-2">
                                    <label>Precio unit.</label>
                                    <input type="number" id="precio_unitario" class="form-control" readonly>
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
                                <table class="table table-sm table-bordered" id="detalleCotTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Producto</th>
                                            <th>Cantidad</th>
                                            <th>Precio Unit.</th>
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
                                    <strong id="totalCot" class="text-primary">$0.00</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                </form>
            </div>
            <div class="modal-footer">
                <button id="btnCancelarCot" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fa-solid fa-times me-2"></i>Cancelar</button>
                <button id="btnFacturarCot" class="btn btn-success"><i class="fa-solid fa-file-invoice-dollar me-2"></i>Facturar</button>
                <button id="btnSaveCot" class="btn btn-primary"><i class="fa-solid fa-save me-2"></i>Guardar Cotización</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(function(){
    // detalle: array de objetos { producto_id, nombre, cantidad(Number), precio_unitario(Number), subtotal(Number) }
    let detalle = [];
    let productos = [];

    cargarCotizaciones();
    cargarClientes();
    cargarProductos();

    $('#btnNuevaCot').click(function(){
        $('#cotForm')[0].reset();
        detalle = [];
        actualizarDetalle();
        $('#id_cotizacion').val('');
        $('#accion').val('guardar');
        generarNumero();
        $('#fecha').val(new Date().toISOString().split('T')[0]);
        const plus7 = new Date(); plus7.setDate(plus7.getDate()+7);
        $('#valida_hasta').val(plus7.toISOString().split('T')[0]);

        setEditable(true);
        $('#badgeEstado').removeClass('bg-secondary').addClass('bg-success').text('ACTIVA');
    });

    function cargarCotizaciones(){
        $.post('Cotizacion_ajax.php', { accion: 'listar' }, function(res){
            let html = '';
            if(res.success){
                res.data.forEach(function(c){
                    let estado = c.activo == 1 ? '<span class="badge bg-success">ACTIVA</span>' : '<span class="badge bg-secondary">FACTURADA</span>';
                        let acciones = '';
                        if(c.activo == 1){
                            acciones = `
                                <button class=\"btn btn-sm btn-success\" onclick=\"convertirFactura(${c.id_cotizaciones})\" title=\"Facturar directo\"><i class=\"fa-solid fa-file-invoice-dollar\"></i></button>
                                <button class=\"btn btn-sm btn-warning\" onclick=\"facturarEditable(${c.id_cotizaciones})\" title=\"Facturar editable\"><i class=\"fa-solid fa-pen-to-square\"></i></button>
                                <button class=\"btn btn-sm btn-primary\" onclick=\"imprimirCot(${c.id_cotizaciones})\" title=\"Imprimir cotización\"><i class=\"fa-solid fa-print\"></i></button>
                                <button class=\"btn btn-sm btn-danger\" onclick=\"anularCot(${c.id_cotizaciones})\" title=\"Anular\"><i class=\"fa-solid fa-ban\"></i></button>
                            `;
                        } else {
                            acciones = `
                                <button class=\"btn btn-sm btn-primary\" onclick=\"imprimirCot(${c.id_cotizaciones})\" title=\"Imprimir cotización\"><i class=\"fa-solid fa-print\"></i></button>
                            `;
                        }
                    html += `<tr>
                        <td>${c.numero_documento}</td>
                        <td>${c.cliente_nombre}</td>
                        <td>${c.fecha}</td>
                        <td>${c.valida_hasta}</td>
                        <td>$${parseFloat(c.total).toFixed(2)}</td>
                        <td>${estado}</td>
                        <td>${acciones}</td>
                    </tr>`;
                });
            }
            $('#cotTable tbody').html(html || '<tr><td colspan="7" class="text-center">No hay cotizaciones</td></tr>');
        }, 'json');
    }

    window.verCot = function(id){
        $.post('Cotizacion_ajax.php', { accion: 'obtener', id: id }, function(res){
            if(!res.success){ Swal.fire('Error', res.message, 'error'); return; }
            const c = res.data;
            $('#id_cotizacion').val(c.id_cotizaciones);
            $('#numero_documento').val(c.numero_documento);
            $('#fecha').val(c.fecha);
            $('#valida_hasta').val(c.valida_hasta);
            $('#cliente_id').val(c.cliente_id);

            // Normalizar detalle: asegurar números
            detalle = (res.detalle || []).map(function(d){
                return {
                    producto_id: String(d.producto_id),
                    nombre: d.nombre || d.nombre_producto || 'Producto',
                    cantidad: Number(d.cantidad) || 0,
                    precio_unitario: Number(d.precio_unitario) || 0,
                    subtotal: Number(d.subtotal ?? (d.cantidad * d.precio_unitario)) || 0
                };
            });

            actualizarDetalle();
            $('#accion').val('editar');

            if(parseInt(c.activo) === 0){
                setEditable(false);
                $('#badgeEstado').removeClass('bg-success').addClass('bg-secondary').text('FACTURADA');
            } else {
                setEditable(true);
                $('#badgeEstado').removeClass('bg-secondary').addClass('bg-success').text('ACTIVA');
            }

            $('#cotModal').modal('show');
        }, 'json');
    };

    // Restaurar función para facturación editable (redirige a pantalla de facturación con pre-carga)
    window.facturarEditable = function(id){
        window.location.href = 'MantFacturacion.php?cotizacion_id=' + id;
    };

    window.imprimirCot = function(id){
        window.open('Cotizacion_imprimir.php?id=' + id, '_blank');
    };

    window.convertirFactura = function(id){
        Swal.fire({
            title: 'Convertir a factura?',
            text: 'Se intentará crear una factura con los mismos ítems (no afectará inventario aquí).',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Si, convertir'
        }).then((r)=> {
            if(!r.isConfirmed) return;
            $.post('Cotizacion_ajax.php', { accion: 'convertir_a_factura', id: id }, function(res){
                if(res.success){
                    Swal.fire('Convertido','Cotización convertida. ID factura: '+res.factura_id,'success');
                    cargarCotizaciones();
                } else Swal.fire('Error', res.message, 'error');
            }, 'json');
        });
    };

    window.anularCot = function(id){
        Swal.fire({
            title:'Anular cotización?',
            text:'Esta acción marcará la cotización como anulada.',
            icon:'warning',
            showCancelButton:true,
            confirmButtonText:'Si, anular'
        }).then((r)=> {
            if(!r.isConfirmed) return;
            $.post('Cotizacion_ajax.php',{ accion:'anular', id:id }, function(res){
                if(res.success){ Swal.fire('Anulado',res.message,'success'); cargarCotizaciones(); }
                else Swal.fire('Error',res.message,'error');
            },'json');
        });
    };


    function cargarClientes(){
        $.post('Cotizacion_ajax.php',{ accion:'listar_clientes' }, function(res){
            if(res.success){
                let opts = '<option value="">Seleccione...</option>';
                res.data.forEach(function(c){ opts += `<option value="${c.id_clientes}">${c.nombre}</option>`; });
                $('#cliente_id').html(opts);
            }
        }, 'json');
    }

    function cargarProductos(){
        $.post('Cotizacion_ajax.php',{ accion:'listar_productos' }, function(res){
            if(res.success){
                productos = res.data;
                let opts = '<option value="">Seleccione un producto...</option>';
                res.data.forEach(function(p){
                    opts += `<option value="${p.id_productos}" data-precio="${p.precio_venta}">${p.nombre} - Stock: ${parseInt(p.stock)||0} - $${parseFloat(p.precio_venta).toFixed(2)}</option>`;
                });
                $('#producto_id').html(opts);
            }
        }, 'json');
    }

    function generarNumero(){
        $.post('Cotizacion_ajax.php',{ accion:'generar_numero' }, function(res){
            if(res.success) $('#numero_documento').val(res.numero);
        }, 'json');
    }

    $('#producto_id').on('change', function(){
        const opt = $(this).find('option:selected');
        const precio = parseFloat(opt.data('precio')) || 0;
        $('#precio_unitario').val(precio.toFixed(2));
        calcularSubItem();
    });
    $('#cantidad').on('input change', calcularSubItem);
    function calcularSubItem(){
        const cant = Number($('#cantidad').val()) || 0;
        const precio = Number($('#precio_unitario').val()) || 0;
        const sub = cant * precio;
        $('#subtotalItem').val('$' + sub.toFixed(2));
    }

    $('#btnAddItem').click(function(){
        if($('#id_cotizacion').val() && $('#accion').val() === 'editar' && $('#badgeEstado').text().toLowerCase().includes('fact')) {
            Swal.fire('Bloqueado','No se puede agregar items a una cotización facturada','warning');
            return;
        }

        const prodId = $('#producto_id').val();
        const producto = productos.find(p => String(p.id_productos) === String(prodId));
        const cantidad = Number($('#cantidad').val()) || 0;
        const precio = Number($('#precio_unitario').val()) || 0;
        if(!prodId){ Swal.fire('Error','Seleccione producto','error'); return; }
        if(cantidad <= 0){ Swal.fire('Error','Cantidad invalida','error'); return; }

        const existente = detalle.find(i => String(i.producto_id) === String(prodId));
        if(existente){
            existente.cantidad = Number(existente.cantidad) + cantidad;
            existente.precio_unitario = precio;
            existente.subtotal = Number(existente.cantidad) * Number(existente.precio_unitario);
        } else {
            detalle.push({
                producto_id: String(prodId),
                nombre: producto.nombre || 'Producto',
                cantidad: Number(cantidad),
                precio_unitario: Number(precio),
                subtotal: Number(cantidad) * Number(precio)
            });
        }

        $('#producto_id').val('');
        $('#cantidad').val('1');
        $('#precio_unitario').val('');
        $('#subtotalItem').val('');
        actualizarDetalle();
    });

    function actualizarDetalle(){
        let html = '';
        let total = 0;
        const bloqueada = $('#badgeEstado').text().toLowerCase().includes('fact');
        detalle.forEach((it, idx) => {
            const cant = Number(it.cantidad) || 0;
            const pu = Number(it.precio_unitario) || 0;
            const sub = Number(it.subtotal) || (cant * pu);
            total += sub;

            it.cantidad = cant;
            it.precio_unitario = pu;
            it.subtotal = sub;

            let acciones = '';
            if(!bloqueada){
                acciones = `
                <div class="btn-group btn-group-sm" role="group">
                    <button type="button" class="btn btn-outline-primary" onclick="decrementarItemCot(${idx})" title="Disminuir"><i class="fa-solid fa-minus"></i></button>
                    <button type="button" class="btn btn-outline-success" onclick="incrementarItemCot(${idx})" title="Aumentar"><i class="fa-solid fa-plus"></i></button>
                    <button type="button" class="btn btn-outline-danger" onclick="removeItem(${idx})" title="Eliminar"><i class="fa-solid fa-trash"></i></button>
                </div>`;
            } else {
                acciones = '<span class="text-muted">--</span>';
            }
            html += `<tr>
                <td>${escapeHtml(it.nombre)}</td>
                <td>${cant}</td>
                <td>$${pu.toFixed(2)}</td>
                <td>$${sub.toFixed(2)}</td>
                <td>${acciones}</td>
            </tr>`;
        });
        $('#detalleCotTable tbody').html(html || '<tr><td colspan="5" class="text-center">No hay items</td></tr>');
        $('#totalCot').text('$' + total.toFixed(2));
    }

    window.incrementarItemCot = function(index){
        if($('#badgeEstado').text().toLowerCase().includes('fact')) return; // bloqueada
        const item = detalle[index];
        if(!item) return;
        item.cantidad = Number(item.cantidad) + 1;
        item.subtotal = Number(item.cantidad) * Number(item.precio_unitario);
        actualizarDetalle();
    };

    window.decrementarItemCot = function(index){
        if($('#badgeEstado').text().toLowerCase().includes('fact')) return; // bloqueada
        const item = detalle[index];
        if(!item) return;
        if(Number(item.cantidad) <= 1){
            Swal.fire({
                title:'¿Eliminar item?',
                text:'Cantidad mínima es 1. ¿Desea eliminar este producto?',
                icon:'question',
                showCancelButton:true,
                confirmButtonText:'Eliminar',
                cancelButtonText:'Cancelar'
            }).then(r=>{ if(r.isConfirmed){ removeItem(index); } });
            return;
        }
        item.cantidad = Number(item.cantidad) - 1;
        item.subtotal = Number(item.cantidad) * Number(item.precio_unitario);
        actualizarDetalle();
    };

    window.removeItem = function(i){
        if($('#id_cotizacion').val() && $('#badgeEstado').text().toLowerCase().includes('fact')) {
            Swal.fire('Bloqueado','No se puede eliminar items de una cotización facturada','warning');
            return;
        }
        detalle.splice(i,1);
        actualizarDetalle();
    };

    $('#btnSaveCot').click(function(){
        if(detalle.length === 0){ Swal.fire('Error','Agregue al menos un item','error'); return; }
        if(!$('#cliente_id').val()){ Swal.fire('Error','Seleccione cliente','error'); return; }
        if(!$('#fecha').val()){ Swal.fire('Error','Seleccione fecha','error'); return; }

        const detallePayload = detalle.map(i => ({
            producto_id: i.producto_id,
            nombre: i.nombre,
            cantidad: Number(i.cantidad) || 0,
            precio_unitario: Number(i.precio_unitario) || 0,
            subtotal: Number(i.subtotal) || (Number(i.cantidad) * Number(i.precio_unitario))
        }));

        const payload = {
            accion: $('#accion').val(),
            id: $('#id_cotizacion').val(),
            numero_documento: $('#numero_documento').val(),
            fecha: $('#fecha').val(),
            valida_hasta: $('#valida_hasta').val(),
            cliente_id: $('#cliente_id').val(),
            detalle: detallePayload
        };

        $.ajax({
            url: 'Cotizacion_ajax.php',
            method: 'POST',
            data: JSON.stringify(payload),
            contentType: 'application/json',
            success: function(res){
                if(res.success){
                    Swal.fire('Éxito', res.message, 'success');
                    $('#cotModal').modal('hide');
                    cargarCotizaciones();
                } else Swal.fire('Error', res.message, 'error');
            },
            error: function(){ Swal.fire('Error','Error procesando la solicitud','error'); }
        });
    });

    $('#btnFacturarCot').click(function(){
        if($('#badgeEstado').text().toLowerCase().includes('fact')) {
            Swal.fire('Información','La cotización ya está facturada','info');
            return;
        }
        if(detalle.length === 0){ Swal.fire('Error','Agregue al menos un item','error'); return; }
        if(!$('#cliente_id').val()){ Swal.fire('Error','Seleccione cliente','error'); return; }
        if(!$('#fecha').val()){ Swal.fire('Error','Seleccione fecha','error'); return; }

        const detallePayload = detalle.map(i => ({
            producto_id: i.producto_id,
            nombre: i.nombre,
            cantidad: Number(i.cantidad) || 0,
            precio_unitario: Number(i.precio_unitario) || 0,
            subtotal: Number(i.subtotal) || (Number(i.cantidad) * Number(i.precio_unitario))
        }));

        const payload = {
            accion: $('#accion').val(),
            id: $('#id_cotizacion').val(),
            numero_documento: $('#numero_documento').val(),
            fecha: $('#fecha').val(),
            valida_hasta: $('#valida_hasta').val(),
            cliente_id: $('#cliente_id').val(),
            detalle: detallePayload
        };

        // Guardar primero (si es nueva o edición) y luego convertir
        $.ajax({
            url: 'Cotizacion_ajax.php',
            method: 'POST',
            data: JSON.stringify(payload),
            contentType: 'application/json',
            success: function(res){
                if(!res.success){ Swal.fire('Error', res.message, 'error'); return; }
                const cot_id = res.id || $('#id_cotizacion').val();
                $('#id_cotizacion').val(cot_id);
                // ahora convertir
                $.post('Cotizacion_ajax.php', { accion:'convertir_a_factura', id: cot_id }, function(conv){
                    if(conv.success){
                        Swal.fire({
                            icon:'success',
                            title:'Facturada',
                            text:'Factura creada ID: '+conv.factura_id,
                            showCancelButton:true,
                            confirmButtonText:'Imprimir Factura',
                            cancelButtonText:'Cerrar'
                        }).then(r=>{ if(r.isConfirmed){ window.open('Factura_imprimir.php?id='+conv.factura_id,'_blank'); } });
                        $('#cotModal').modal('hide');
                        cargarCotizaciones();
                    } else {
                        Swal.fire('Error', conv.message, 'error');
                    }
                }, 'json');
            },
            error: function(){ Swal.fire('Error','Error procesando la solicitud','error'); }
        });
    });

    function setEditable(flag){
        $('#cotModal').find('input, select, button#btnAddItem').prop('disabled', !flag);
        if(flag){
            $('#btnSaveCot').show();
            $('#btnFacturarCot').show();
        } else {
            $('#btnSaveCot').hide();
            $('#btnFacturarCot').hide();
        }
    }

    function escapeHtml(text) {
        if (typeof text !== 'string') return text;
        return text.replace(/[&<>"'\/]/g, function (s) {
          const entityMap = {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;','/':'&#x2F;'};
          return entityMap[s];
        });
    }

});
</script>

<?php include '../includes/footer.php'; ?>
