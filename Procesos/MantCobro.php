<?php
session_start();
if(!isset($_SESSION['usuario'])) {
    header("Location: ../index.html");
    exit();
}
require_once '../conexion.php';
include '../includes/header.php';
include '../includes/sidebar.php';

// obtener id usuario para enviar en JS
$usuario_id = $_SESSION['user_info']['id_usuarios'] ?? '';
?>
<div class="main-content">
    <div class="main-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col">
                    <h4 class="page-title"><i class="fa-solid fa-hand-holding-dollar me-2"></i>Cobros</h4>
                    <p class="page-subtitle">Registrar cobros de clientes</p>
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
                <button id="btnNuevoCobro" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#cobroModal">
                    <i class="fa-solid fa-plus me-2"></i>Nuevo Cobro
                </button>
            </div>

            <div class="card card-custom fade-in">
                <div class="card-header card-header-custom">
                    <h5 class="mb-0"><i class="fa-solid fa-wallet me-2"></i>Cobros Recientes</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-mantenimiento" id="cobroTable">
                            <thead>
                                <tr>
                                    <th>N° Cobro</th>
                                    <th>Cliente</th>
                                    <th>Fecha</th>
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

<!-- Modal Cobro -->
<div class="modal fade" id="cobroModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa-solid fa-hand-holding-dollar me-2"></i> Registrar Cobro</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="cobroForm">
                    <input type="hidden" id="id_cobro" name="id_cobro">
                    <input type="hidden" id="accion" name="accion" value="guardar">
                    <input type="hidden" id="usuario_id" name="usuario_id" value="<?php echo htmlspecialchars($usuario_id); ?>">

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Cliente</label>
                            <select id="cliente_id" name="cliente_id" class="form-select" required>
                                <option value="">Seleccione...</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Fecha de Cobro</label>
                            <input type="date" id="fecha_cobro" name="fecha_cobro" class="form-control" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Método</label>
                            <select id="metodo_cobro" name="metodo_cobro" class="form-select">
                                <option value="Efectivo">Efectivo</option>
                                <option value="Tarjeta">Tarjeta</option>
                                <option value="Transferencia">Transferencia</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Referencia / Nota</label>
                            <input type="text" id="nota_cobro" name="nota" class="form-control" placeholder="Referencia, transferencia, etc.">
                        </div>
                    </div>

                    <div class="card mb-3">
                        <div class="card-header bg-light"><h6 class="mb-0"><i class="fa-solid fa-file-invoice-dollar me-2"></i>Facturas pendientes del cliente</h6></div>
                        <div class="card-body">
                            <div class="row g-3 mb-2 align-items-end">
                                <div class="col-md-3">
                                    <label>Total cobro</label>
                                    <input type="number" id="monto_total_cobro" class="form-control" min="0" step="0.01" placeholder="0.00">
                                </div>
                                <div class="col-md-3">
                                    <button type="button" id="btnDistribuir" class="btn btn-outline-primary w-100">Distribuir automáticamente</button>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted"></small>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-sm table-bordered" id="facturasPendientesTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>N° Factura</th>
                                            <th>Fecha</th>
                                            <th>Total</th>
                                            <th>Saldo</th>
                                            <th>Monto a aplicar</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr><td colspan="5" class="text-center text-muted">Seleccione un cliente para ver facturas pendientes</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="row justify-content-end">
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-body d-flex justify-content-between">
                                    <strong>Total aplicado:</strong>
                                    <strong id="totalAplicado" class="text-primary">$0.00</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                </form>
            </div>
            <div class="modal-footer">
                <button id="btnCancelarCobro" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fa-solid fa-times me-2"></i>Cancelar</button>
                <button id="btnSaveCobro" class="btn btn-primary"><i class="fa-solid fa-save me-2"></i>Registrar Cobro</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(function(){

    let facturas = [];

    // ============================
    // ASIGNAR FECHA AUTOMÁTICA AL ABRIR MODAL
    // ============================
    $('#cobroModal').on('show.bs.modal', function() {
        const hoy = new Date().toISOString().split('T')[0];
        $('#fecha_cobro').val(hoy);
    });

    // ============================
    // CARGAR COBROS
    // ============================
    function cargarCobros(){
        $.ajax({
            url: "Cobro_ajax.php",
            method: "POST",
            data: { accion: "listar" },
            dataType: "json",
            success: function(res){
                let html = "";
                if(res.success){
                    res.data.forEach(c => {
                        const estado = (c.activo == 1)
                            ? '<span class="badge bg-success">ACTIVO</span>'
                            : '<span class="badge bg-secondary">ANULADO</span>';

                        html += `
                            <tr>
                                <td>${c.numero_documento}</td>
                                <td>${c.cliente_nombre || ''}</td>
                                <td>${c.fecha}</td>
                                <td>${c.usuario_nombre || ''}</td>
                                <td>${estado}</td>
                                <td>
                                    <button class="btn btn-primary btn-sm" onclick="imprimirCobro(${c.id_cobros})">
                                        <i class="fa fa-print"></i>
                                    </button>
                                </td>
                            </tr>`;
                    });
                }
                $("#cobroTable tbody").html(html || '<tr><td colspan="7" class="text-center">No hay cobros</td></tr>');
            }
        });
    }

    // ============================
    // LISTAR CLIENTES
    // ============================
    function cargarClientes(){
        $.ajax({
            url: "Cobro_ajax.php",
            method: "POST",
            data: { accion: "listar_clientes" },
            dataType: "json",
            success: function(res){
                if(res.success){
                    let opts = '<option value="">Seleccione...</option>';
                    res.data.forEach(cli => {
                        opts += `<option value="${cli.id_clientes}">${cli.nombre}</option>`;
                    });
                    $("#cliente_id").html(opts);
                }
            }
        });
    }

    cargarCobros();
    cargarClientes();

    // ============================
    // CAMBIO DE CLIENTE → CARGAR FACTURAS
    // ============================
    $("#cliente_id").on("change", function(){
        const cid = $(this).val();

        if(!cid){
            facturas = [];
            $('#facturasPendientesTable tbody').html(
                '<tr><td colspan="5" class="text-center text-muted">Seleccione un cliente para ver facturas</td></tr>'
            );
            return;
        }

        $.ajax({
            url: "Cobro_ajax.php",
            method: "POST",
            data: { accion: "facturasCliente", cliente_id: cid },
            dataType: "json",
            success: function(res){
                facturas = res.success ? res.data : [];
                renderFacturas();
            }
        });
    });

    // ============================
    // RENDER FACTURAS
    // ============================
    function renderFacturas(){
        if(!facturas.length){
            $('#facturasPendientesTable tbody').html(
                '<tr><td colspan="5" class="text-center text-muted">No hay facturas pendientes</td></tr>'
            );
            updateTotalAplicado();
            return;
        }

        let html = "";
        facturas.forEach(f => {

            const saldo = parseFloat(f.pendiente ?? 0);

            html += `
                <tr data-id="${f.id_facturas}">
                    <td>${f.numero_documento}</td>
                    <td>${f.fecha}</td>
                    <td>$${parseFloat(f.total).toFixed(2)}</td>
                    <td>$${saldo.toFixed(2)}</td>
                    <td>
                        <input type="number"
                               class="form-control montoAplicar"
                               min="0"
                               step="0.01"
                               data-saldo="${saldo}"
                               placeholder="0.00"
                               style="width:140px;">
                    </td>
                </tr>`;
        });

        $("#facturasPendientesTable tbody").html(html);

        $(".montoAplicar").on("input change", function(){
            let val = parseFloat($(this).val()) || 0;
            let saldo = parseFloat($(this).data("saldo")) || 0;

            if(val > saldo) $(this).val(saldo.toFixed(2));

            updateTotalAplicado();
        });

        updateTotalAplicado();
    }

    // ============================
    // DISTRIBUIR AUTOMÁTICAMENTE
    // ============================
    $("#btnDistribuir").click(function(){

        const total = parseFloat($("#monto_total_cobro").val()) || 0;
        if(total <= 0){
            Swal.fire("Error", "Ingrese un monto total primero", "error");
            return;
        }

        if(!facturas.length){
            Swal.fire("Error", "No hay facturas para distribuir", "error");
            return;
        }

        let restante = total;

        $(".montoAplicar").each(function(){
            if(restante <= 0){
                $(this).val("");
                return;
            }

            const saldo = parseFloat($(this).data("saldo")) || 0;
            const aplicar = Math.min(saldo, restante);

            $(this).val(aplicar.toFixed(2));
            restante -= aplicar;
        });

        updateTotalAplicado();
    });

    // ============================
    // TOTAL APLICADO
    // ============================
    function updateTotalAplicado(){
        let total = 0;
        $(".montoAplicar").each(function(){
            total += parseFloat($(this).val()) || 0;
        });
        $("#totalAplicado").text("$" + total.toFixed(2));
    }

    // ============================
    // GUARDAR COBRO — JSON CORRECTO
    // ============================
    $("#btnSaveCobro").click(function(){

        const detalles = [];

        $("#facturasPendientesTable tbody tr").each(function(){
            const id = $(this).data("id");
            const monto = parseFloat($(this).find(".montoAplicar").val()) || 0;

            if(monto > 0){
                detalles.push({
                    factura_id: id,
                    monto_aplicado: monto
                });
            }
        });

        if(detalles.length === 0){
            Swal.fire("Error","Debe aplicar montos a alguna factura","error");
            return;
        }

        const payload = {
            accion: "registrar",
            cliente_id: $("#cliente_id").val(),
            fecha: $("#fecha_cobro").val(),
            metodo: $("#metodo_cobro").val(),
            nota: $("#nota_cobro").val(),
            usuario_id: $("#usuario_id").val(),
            monto_total: parseFloat($("#monto_total_cobro").val()) || 0,
            detalle: detalles
        };

        $.ajax({
    url: "Cobro_ajax.php",
    method: "POST",
    data: JSON.stringify(payload),
    contentType: "application/json; charset=utf-8",
    dataType: "json",

    success: function(res){
        if(res.success){

            Swal.fire({
                icon: "success",
                title: "Cobro registrado",
                text: res.message,
                showCancelButton: true,
                confirmButtonText: '<i class="fa-solid fa-print"></i> Imprimir'
            }).then(r=>{
                if(r.isConfirmed){
                    window.open("Cobro_imprimir.php?id=" + res.cobro_id, "_blank");
                }
            });

            // 🟦 Cerrar modal
            $("#cobroModal").modal("hide");

            // 🟦 LIMPIAR FORMULARIO COMPLETAMENTE
            $("#cliente_id").val("").trigger("change");
            $("#fecha").val("");
            $("#metodo_pago").val("Efectivo");
            $("#nota").val("");
            $("#monto").val("");
            $("#tablaFacturas tbody").html("");
            totalAplicado = 0; // si usas variable global

            // 🟩 Actualizar listado de cobros sin recargar
            cargarCobros();

        } else {
            Swal.fire("Error", res.message, "error");
        }
    },

    error: function(){
        Swal.fire("Error","Error procesando la solicitud","error");
    }
});


    });

    // ============================
    // IMPRIMIR
    // ============================
    window.imprimirCobro = function(id){
        window.open("Cobro_imprimir.php?id=" + id, "_blank");
    };

});
</script>


<?php include '../includes/footer.php'; ?>
