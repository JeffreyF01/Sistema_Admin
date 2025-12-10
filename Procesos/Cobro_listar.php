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
                        <i class="fa-solid fa-list me-2"></i>Listado de Cobros
                    </h4>
                    <p class="page-subtitle">Visualiza y gestiona los cobros registrados</p>
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
            <!-- filtros -->
            <div class="card card-custom fade-in mb-4">
                <div class="card-header card-header-custom">
                    <h5 class="mb-0"><i class="fa-solid fa-filter me-2"></i>Filtros</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label for="filtroNumero" class="form-label">N° Cobro</label>
                            <input type="text" class="form-control" id="filtroNumero" placeholder="COB-00000001">
                        </div>
                        <div class="col-md-3">
                            <label for="filtroCliente" class="form-label">Cliente</label>
                            <input type="text" class="form-control" id="filtroCliente" placeholder="Nombre del cliente">
                        </div>
                        <div class="col-md-2">
                            <label for="filtroDesde" class="form-label">Desde</label>
                            <input type="date" class="form-control" id="filtroDesde">
                        </div>
                        <div class="col-md-2">
                            <label for="filtroHasta" class="form-label">Hasta</label>
                            <input type="date" class="form-control" id="filtroHasta">
                        </div>
                        <div class="col-md-2">
                            <label for="filtroEstado" class="form-label">Estado</label>
                            <select class="form-select" id="filtroEstado">
                                <option value="">Todos</option>
                                <option value="1" selected>Activos</option>
                                <option value="0">Anulados</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- acciones -->
            <div class="mb-3">
                <a href="MantCobro.php" class="btn btn-primary">
                    <i class="fa-solid fa-plus me-2"></i>Nuevo Cobro
                </a>
                <button class="btn btn-success" id="exportarExcel">
                    <i class="fa-solid fa-file-excel me-2"></i>Exportar a Excel
                </button>
            </div>

            <!-- tabla -->
            <div class="card card-custom fade-in">
                <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fa-solid fa-receipt me-2"></i>Cobros Registrados</h5>
                    <span class="badge bg-primary" id="contadorCobros">0 cobros</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-mantenimiento" id="cobroTable">
                            <thead>
                                <tr>
                                    <th>N° Cobro</th>
                                    <th>Cliente</th>
                                    <th>Fecha</th>
                                    <th>Método</th>
                                    <th>Usuario</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- llenado por JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Modal detalle cobro -->
<div class="modal fade" id="detalleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa-solid fa-file-invoice me-2"></i>Detalle de Cobro</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detalleContent">
                <!-- contenido dinámico -->
                <div class="text-center py-4">Cargando...</div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button id="imprimirDesdeDetalle" class="btn btn-primary"><i class="fa-solid fa-print me-2"></i>Imprimir</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(function(){
    let cobros = [];
    let cobroActualId = null;

    cargarCobros();
    // filtros - recalcular en cada cambio
    $('#filtroNumero,#filtroCliente,#filtroDesde,#filtroHasta,#filtroEstado').on('change keyup', function(){
        aplicarFiltros();
    });

    // cargar cobros desde ajax
    function cargarCobros(){
        $.post("Cobro_ajax.php", { accion: "listar" }, function(res){
            cobros = (res.success) ? res.data : [];
            renderTabla();
            aplicarFiltros();
        }, "json").fail(function(xhr){
            console.error("Error cargando cobros:", xhr.responseText);
            $('#cobroTable tbody').html('<tr><td colspan="8" class="text-center">Error cargando cobros</td></tr>');
        });
    }

    function renderTabla(){
        let html = '';
        if(!cobros || cobros.length === 0){
            html = '<tr><td colspan="8" class="text-center py-4"><i class="fa-solid fa-inbox fa-2x text-muted mb-3"></i><br><span class="text-muted">No hay cobros registrados</span></td></tr>';
            $("#cobroTable tbody").html(html);
            return;
        }

        cobros.forEach(c => {
            const estado = (c.activo == 1) ? '<span class="badge bg-success">ACTIVO</span>' : '<span class="badge bg-secondary">ANULADO</span>';
            html += `<tr data-id="${c.id_cobros}" data-numero="${(c.numero_documento||'').toString()}" data-cliente="${(c.cliente_nombre||'').toString()}" data-fecha="${(c.fecha||'').toString()}" data-activo="${c.activo}">
                <td><strong>${c.numero_documento || ('COB-' + c.id_cobros)}</strong></td>
                <td>${c.cliente_nombre || ''}</td>
                <td>${c.fecha || ''}</td>
                <td>${c.metodo_pago || ''}</td>
                <td>${c.usuario_nombre || ''}</td>
                <td>${estado}</td>
                <td>
                    <button class="btn btn-sm btn-info" onclick="verDetalle(${c.id_cobros})" title="Ver detalle"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn btn-sm btn-primary" onclick="imprimirCobro(${c.id_cobros})" title="Imprimir"><i class="fa-solid fa-print"></i></button>
                    ${c.activo == 1 ? `<button class="btn btn-sm btn-danger" onclick="anularCobro(${c.id_cobros})" title="Anular"><i class="fa-solid fa-ban"></i></button>` : ''}
                </td>
            </tr>`;
        });

        $("#cobroTable tbody").html(html);
        $('#contadorCobros').text(cobros.length + ' cobro(s)');
    }

    function aplicarFiltros(){
        const numero = $('#filtroNumero').val().toLowerCase();
        const cliente = $('#filtroCliente').val().toLowerCase();
        const desde = $('#filtroDesde').val();
        const hasta = $('#filtroHasta').val();
        const estado = $('#filtroEstado').val(); // '' or '1' or '0'
        let visibles = 0;

        $('#cobroTable tbody tr').each(function(){
            const tr = $(this);
            const rowNumero = (tr.data('numero') || '').toString().toLowerCase();
            const rowCliente = (tr.data('cliente') || '').toString().toLowerCase();
            const rowFecha = tr.data('fecha') || '';
            const rowActivo = tr.data('activo').toString();

            let mostrar = true;
            if(numero && !rowNumero.includes(numero)) mostrar = false;
            if(cliente && !rowCliente.includes(cliente)) mostrar = false;
            if(desde && rowFecha < desde) mostrar = false;
            if(hasta && rowFecha > hasta) mostrar = false;
            if(estado !== '' && rowActivo !== estado) mostrar = false;

            if(mostrar){ tr.show(); visibles++; } else { tr.hide(); }
        });

        $('#contadorCobros').text(visibles + ' cobro(s)');
    }

    // ver detalle - usa Cobro_ajax.php accion 'obtener' (debe existir en el backend)
    window.verDetalle = function(id){
        cobroActualId = id;
        $('#detalleContent').html('<div class="text-center py-4">Cargando...</div>');
        $('#detalleModal').modal('show');

        $.post("Cobro_ajax.php", { accion: "obtener", id: id }, function(res){
            if(!res.success){
                $('#detalleContent').html('<div class="text-center text-danger">No se pudo obtener el detalle</div>');
                return;
            }
            const c = res.data;
            // construir HTML
            let html = `<div class="row mb-3">
                <div class="col-md-6">
                    <p><strong>N° Cobro:</strong> ${c.numero_documento || ''}</p>
                    <p><strong>Cliente:</strong> ${c.cliente_nombre || ''}</p>
                    <p><strong>Fecha:</strong> ${c.fecha || ''}</p>
                    <p><strong>Método:</strong> ${c.metodo_pago || ''}</p>
                </div>
                <div class="col-md-6 text-end">
                    <p><strong>Usuario:</strong> ${c.usuario_nombre || ''}</p>
                    <p><strong>Estado:</strong> ${c.activo == 1 ? '<span class="badge bg-success">ACTIVO</span>' : '<span class="badge bg-secondary">ANULADO</span>'}</p>
                </div>
            </div>`;

            // detalle aplicado (c.detalle expected array)
            if(Array.isArray(c.detalle) && c.detalle.length > 0){
                html += `<div class="table-responsive"><table class="table table-sm table-bordered">
                    <thead class="table-light"><tr><th>N° Factura</th><th>Fecha</th><th>Total</th><th>Monto Aplicado</th></tr></thead><tbody>`;
                c.detalle.forEach(d => {
                    html += `<tr>
                        <td>${d.numero_documento || d.numero_factura || ('FAC-' + d.factura_id)}</td>
                        <td>${d.fecha || d.fecha_factura || ''}</td>
                        <td>$${(parseFloat(d.total || d.total_factura || 0)).toFixed(2)}</td>
                        <td>$${(parseFloat(d.monto_aplicado || d.monto || 0)).toFixed(2)}</td>
                    </tr>`;
                });
                html += `</tbody></table></div>`;
            } else {
                html += `<div class="alert alert-info">No hay facturas aplicadas en este cobro.</div>`;
            }

            $('#detalleContent').html(html);
        }, "json").fail(function(xhr){
            console.error("Error obtener detalle:", xhr.responseText);
            $('#detalleContent').html('<div class="text-center text-danger">Error obteniendo detalle</div>');
        });
    };

    // imprimir cobro
    window.imprimirCobro = function(id){
        window.open('Cobro_imprimir.php?id=' + id, '_blank');
    };

    $('#imprimirDesdeDetalle').click(function(){
        if(cobroActualId) imprimirCobro(cobroActualId);
    });

    // anular cobro (llama a Cobro_ajax.php con accion 'anular')
    window.anularCobro = function(id){
        Swal.fire({
            title: '¿Está seguro?',
            text: 'Se anulará el cobro (acción reversible según tu lógica).',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, anular',
            cancelButtonText: 'Cancelar'
        }).then(result => {
            if(!result.isConfirmed) return;
            $.post("Cobro_ajax.php", { accion: "anular", id: id }, function(res){
                if(res.success){
                    Swal.fire('Anulado', res.message || 'Cobro anulado', 'success');
                    cargarCobros();
                } else {
                    Swal.fire('Error', res.message || 'No se pudo anular', 'error');
                }
            }, "json").fail(function(xhr){
                console.error("Error anular:", xhr.responseText);
                Swal.fire('Error','Error procesando la solicitud','error');
            });
        });
    };

    // exportar - placeholder
    $('#exportarExcel').click(function(){
        Swal.fire('Info','Funcionalidad de exportación no implementada', 'info');
    });

});
</script>

<?php include '../includes/footer.php'; ?>
