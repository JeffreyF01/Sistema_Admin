<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: ../index.html');
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
                    <h4 class="page-title"><i class="fa-solid fa-boxes-stacked me-2"></i>Movimientos de Inventario</h4>
                    <p class="page-subtitle">Registrar ajustes de entrada o salida afectando existencias directamente</p>
                </div>
                <div class="col-auto">
                    <div class="user-info">
                        <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['usuario'], 0, 1)); ?></div>
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
                <button id="btnNuevoMovimiento" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#movModal">
                    <i class="fa-solid fa-plus me-2"></i>Nuevo Movimiento
                </button>
            </div>

            <div class="card card-custom fade-in">
                <div class="card-header card-header-custom">
                    <h5 class="mb-0"><i class="fa-solid fa-clipboard-list me-2"></i>Movimientos recientes</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-mantenimiento" id="movTable">
                            <thead>
                                <tr>
                                    <th>N°</th>
                                    <th>Tipo</th>
                                    <th>Clase</th>
                                    <th>Fecha</th>
                                    <th>Líneas</th>
                                    <th>Total Cant.</th>
                                    <th>Referencia</th>
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

<!-- Modal Movimiento -->
<div class="modal fade" id="movModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa-solid fa-right-left me-2"></i>Registrar Movimiento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="movForm">
                    <input type="hidden" id="accion" value="guardar">
                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <label class="form-label">N° Documento</label>
                            <input type="text" id="numero_documento" class="form-control" readonly required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Fecha</label>
                            <input type="date" id="fecha" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Tipo de movimiento</label>
                            <select id="tipo_movimiento_id" class="form-select" required>
                                <option value="">Seleccione...</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Clase</label>
                            <input type="text" id="clase_label" class="form-control" readonly>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Referencia / Nota</label>
                            <input type="text" id="referencia" class="form-control" placeholder="Ej: Ajuste por inventario físico">
                        </div>
                    </div>

                    <div class="card mb-3">
                        <div class="card-header bg-light"><h6 class="mb-0"><i class="fa-solid fa-cubes me-2"></i>Detalle</h6></div>
                        <div class="card-body">
                            <div class="row g-3 mb-3">
                                <div class="col-md-5">
                                    <label>Producto</label>
                                    <select id="producto_id" class="form-select">
                                        <option value="">Seleccione...</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label>Cantidad</label>
                                    <input type="number" id="cantidad" class="form-control" min="0.01" step="0.01" value="1">
                                </div>
                                <div class="col-md-3">
                                    <label>Costo unit. (opcional)</label>
                                    <input type="number" id="costo_unitario" class="form-control" step="0.01" placeholder="Solo referencia">
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="button" id="btnAddItem" class="btn btn-success w-100"><i class="fa-solid fa-plus"></i></button>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-sm table-bordered" id="detalleTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Producto</th>
                                            <th>SKU</th>
                                            <th>Cantidad</th>
                                            <th>Costo</th>
                                            <th>Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal"><i class="fa-solid fa-times me-2"></i>Cancelar</button>
                <button id="btnSaveMov" class="btn btn-primary"><i class="fa-solid fa-save me-2"></i>Guardar movimiento</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(function(){
    let detalle = [];
    let productos = [];
    let claseActual = '';

    cargarMovimientos();
    cargarTipos();
    cargarProductos();
    generarNumero();

    $('#movModal').on('shown.bs.modal', function(){
        resetForm();
        generarNumero();
    });

    function resetForm(){
        detalle = [];
        $('#movForm')[0].reset();
        $('#fecha').val(new Date().toISOString().split('T')[0]);
        $('#detalleTable tbody').html('<tr><td colspan="5" class="text-center">Sin ítems</td></tr>');
    }

    function cargarMovimientos(){
        $.post('Movimiento_ajax.php', { accion:'listar' }, function(res){
            let html = '';
            if(res.success){
                res.data.forEach(function(m){
                    const badge = m.clase === 'ENTRADA' ? '<span class="badge bg-success">ENTRADA</span>' : '<span class="badge bg-danger">SALIDA</span>';
                    html += `<tr>
                        <td>${m.numero_documento}</td>
                        <td>${m.tipo_nombre}</td>
                        <td>${badge}</td>
                        <td>${m.fecha}</td>
                        <td>${m.lineas}</td>
                        <td>${parseFloat(m.total_cantidad).toFixed(2)}</td>
                        <td>${m.referencia || ''}</td>
                        <td class="d-flex gap-1">
                            <button class="btn btn-sm btn-primary" title="Ver detalle" onclick="verMovimiento('${m.numero_documento}')"><i class="fa-solid fa-eye"></i></button>
                            <button class="btn btn-sm btn-secondary" title="Imprimir" onclick="imprimirMovimiento(${m.id})"><i class="fa-solid fa-print"></i></button>
                        </td>
                    </tr>`;
                });
            }
            $('#movTable tbody').html(html || '<tr><td colspan="8" class="text-center">Sin movimientos</td></tr>');
        }, 'json');
    }

    function cargarTipos(){
        $.post('Movimiento_ajax.php', { accion:'listar_tipos' }, function(res){
            if(res.success){
                let opts = '<option value="">Seleccione...</option>';
                res.data.forEach(function(t){ opts += `<option value="${t.id_tipos_movimiento}" data-clase="${t.clase}">${t.nombre}</option>`; });
                $('#tipo_movimiento_id').html(opts);
            }
        }, 'json');
    }

    function cargarProductos(){
        $.post('Movimiento_ajax.php', { accion:'listar_productos' }, function(res){
            if(res.success){
                productos = res.data;
                let opts = '<option value="">Seleccione...</option>';
                res.data.forEach(function(p){ opts += `<option value="${p.id_productos}" data-stock="${p.stock}" data-sku="${p.sku}" data-costo="${p.costo}">${p.nombre} — Stock: ${parseFloat(p.stock).toFixed(2)}</option>`; });
                $('#producto_id').html(opts);
            }
        }, 'json');
    }

    function generarNumero(){
        $.post('Movimiento_ajax.php', { accion:'generar_numero' }, function(res){
            if(res.success) $('#numero_documento').val(res.numero);
        }, 'json');
    }

    $('#tipo_movimiento_id').on('change', function(){
        const clase = $(this).find('option:selected').data('clase') || '';
        claseActual = clase;
        $('#clase_label').val(clase);
    });

    $('#btnAddItem').click(function(){
        const prodId = $('#producto_id').val();
        const cantidad = parseFloat($('#cantidad').val()) || 0;
        const costo = $('#costo_unitario').val() === '' ? null : parseFloat($('#costo_unitario').val());
        if(!prodId){ Swal.fire('Error','Seleccione un producto','error'); return; }
        if(cantidad <= 0){ Swal.fire('Error','Cantidad inválida','error'); return; }

        const prod = productos.find(p => p.id_productos == prodId);
        const yaUsado = detalle.filter(d => d.producto_id == prodId).reduce((acc, cur) => acc + cur.cantidad, 0);
        if(claseActual === 'SALIDA'){
            const disponible = parseFloat(prod.stock) - yaUsado;
            if(cantidad > disponible){
                Swal.fire('Error', `Stock insuficiente. Disponible: ${disponible.toFixed(2)}`, 'error');
                return;
            }
        }

        const existente = detalle.find(d => d.producto_id == prodId);
        if(existente){
            existente.cantidad += cantidad;
            if(costo !== null) existente.costo_unitario = costo;
        } else {
            detalle.push({
                producto_id: prodId,
                nombre: prod.nombre,
                sku: prod.sku,
                cantidad: cantidad,
                costo_unitario: costo,
                stock: prod.stock
            });
        }

        $('#producto_id').val('');
        $('#cantidad').val('1');
        $('#costo_unitario').val('');
        renderDetalle();
    });

    function renderDetalle(){
        if(detalle.length === 0){
            $('#detalleTable tbody').html('<tr><td colspan="5" class="text-center">Sin ítems</td></tr>');
            return;
        }

        let html = '';
        detalle.forEach((d, idx) => {
            const costoTxt = d.costo_unitario !== null && d.costo_unitario !== undefined ? `$${parseFloat(d.costo_unitario).toFixed(2)}` : '-';
            html += `<tr>
                <td>${d.nombre}</td>
                <td>${d.sku || ''}</td>
                <td>${parseFloat(d.cantidad).toFixed(2)}</td>
                <td>${costoTxt}</td>
                <td><button class="btn btn-sm btn-danger" onclick="removeItem(${idx})"><i class="fa-solid fa-trash"></i></button></td>
            </tr>`;
        });
        $('#detalleTable tbody').html(html);
    }

    window.removeItem = function(i){
        detalle.splice(i,1);
        renderDetalle();
    }

    $('#btnSaveMov').click(function(){
        if(!$('#tipo_movimiento_id').val()){ Swal.fire('Error','Seleccione un tipo de movimiento','error'); return; }
        if(!$('#fecha').val()){ Swal.fire('Error','Seleccione fecha','error'); return; }
        if(detalle.length === 0){ Swal.fire('Error','Agregue al menos un producto','error'); return; }

        const payload = {
            accion: 'guardar',
            numero_documento: $('#numero_documento').val(),
            fecha: $('#fecha').val(),
            tipo_movimiento_id: $('#tipo_movimiento_id').val(),
            referencia: $('#referencia').val(),
            detalle: detalle
        };

        $.ajax({
            url: 'Movimiento_ajax.php',
            method: 'POST',
            data: JSON.stringify(payload),
            contentType: 'application/json',
            success: function(res){
                if(res.success){
                    Swal.fire('Éxito', res.message, 'success');
                    $('#movModal').modal('hide');
                    cargarMovimientos();
                } else {
                    Swal.fire('Error', res.message, 'error');
                }
            },
            error: function(){
                Swal.fire('Error','No se pudo procesar la solicitud','error');
            }
        });
    });
});

// Ver detalle en modal emergente
function verMovimiento(numero){
    $.post('Movimiento_ajax.php', { accion:'obtener', numero_documento: numero }, function(res){
        if(!res.success){ Swal.fire('Error', res.message, 'error'); return; }
        const cab = res.data;
        let rows = '';
        cab.detalle.forEach(function(d){
            const costoTxt = d.costo_unitario !== null ? `$${parseFloat(d.costo_unitario).toFixed(2)}` : '-';
            rows += `<tr><td>${d.producto_nombre}</td><td>${d.sku || ''}</td><td>${parseFloat(d.cantidad).toFixed(2)}</td><td>${costoTxt}</td></tr>`;
        });
        const badge = cab.clase === 'ENTRADA' ? '<span class="badge bg-success">ENTRADA</span>' : '<span class="badge bg-danger">SALIDA</span>';
        const html = `
            <div class="text-start">
                <p><strong>Documento:</strong> ${cab.numero_documento}</p>
                <p><strong>Tipo:</strong> ${cab.tipo_nombre} ${badge}</p>
                <p><strong>Fecha:</strong> ${cab.fecha}</p>
                <p><strong>Referencia:</strong> ${cab.referencia || ''}</p>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead><tr><th>Producto</th><th>SKU</th><th>Cantidad</th><th>Costo</th></tr></thead>
                        <tbody>${rows}</tbody>
                    </table>
                </div>
            </div>`;
        Swal.fire({ title: 'Detalle de movimiento', html: html, width: 800 });
    }, 'json');
}

function imprimirMovimiento(id){
    if(!id){ Swal.fire('Error','Movimiento no válido','error'); return; }
    window.open('Movimiento_imprimir.php?id=' + id, '_blank');
}
</script>

<?php include '../includes/footer.php'; ?>
