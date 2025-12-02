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
                    <h4 class="page-title"><i class="fa-solid fa-hand-holding-dollar me-2"></i>Pagos a Proveedores</h4>
                    <p class="page-subtitle">Registrar pagos y aplicarlos a una o varias compras (CxP)</p>
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

    <!-- CONTENIDO -->
    <div class="content-area">
        <div class="container-fluid">
            <div class="mb-3 d-flex justify-content-end">
                <button id="btnNuevoPago" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#pagoModal">
                    <i class="fa-solid fa-plus me-2"></i>Nuevo Pago
                </button>
                <a href="Pago_listar.php" class="btn btn-secondary ms-2"><i class="fa-solid fa-list me-2"></i>Ver Pagos</a>
            </div>

            <div class="card card-custom fade-in">
                <div class="card-header card-header-custom">
                    <h5 class="mb-0"><i class="fa-solid fa-wallet me-2"></i>Pagos Recientes</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-mantenimiento" id="pagoTable">
                            <thead>
                                <tr>
                                    <th>N° Pago</th>
                                    <th>Proveedor</th>
                                    <th>Fecha</th>
                                    <th>Monto</th>
                                    <th>Usuario</th>
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

<!-- Modal Pago -->
<div class="modal fade" id="pagoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title"><i class="fa-solid fa-hand-holding-dollar me-2"></i> Registrar Pago a Proveedor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <form id="pagoForm">

                    <input type="hidden" id="usuario_id" value="<?php echo $_SESSION['user_info']['id_usuarios']; ?>">


                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Proveedor</label>
                            <select id="proveedor_pago_id" class="form-select" required>
                                <option value="">Seleccione...</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Fecha de Pago</label>
                            <input type="date" id="fecha_pago" class="form-control" required>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Método</label>
                            <select id="metodo_pago" class="form-select">
                                <option value="Efectivo">Efectivo</option>
                                <option value="Transferencia">Transferencia</option>
                                <option value="Cheque">Cheque</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Referencia / Nota</label>
                            <input type="text" id="nota_pago" class="form-control">
                        </div>
                    </div>

                    <!-- FACTURAS -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fa-solid fa-file-invoice-dollar me-2"></i>Facturas pendientes del proveedor</h6>
                        </div>

                        <div class="card-body">

                            <div class="row g-3 mb-2 align-items-end">
                                <div class="col-md-3">
                                    <label>Total pago</label>
                                    <input type="number" id="monto_total_pago" class="form-control" step="0.01">
                                </div>

                                <div class="col-md-3">
                                    <button type="button" id="btnDistribuir" class="btn btn-outline-primary w-100">
                                        Distribuir automáticamente
                                    </button>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-sm table-bordered" id="facturasPendientesTable">
                                    <thead>
                                        <tr>
                                            <th>N° Compra</th>
                                            <th>Fecha</th>
                                            <th>Total</th>
                                            <th>Saldo</th>
                                            <th>Monto a aplicar</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr><td colspan="5" class="text-center text-muted">Seleccione un proveedor</td></tr>
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
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button id="btnSavePago" class="btn btn-primary">Registrar Pago</button>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>

$(function(){

    let facturas = [];

    cargarPagos();
    cargarProveedores();

    $("#btnNuevoPago").click(() => {
        $("#pagoForm")[0].reset();
        $("#facturasPendientesTable tbody").html('<tr><td colspan="5" class="text-center text-muted">Seleccione un proveedor</td></tr>');
        $("#fecha_pago").val(new Date().toISOString().split('T')[0]);
    });

    function cargarPagos(){
        $.post("Pago_ajax.php", {accion:"listar"}, function(res){
            let html = "";
            if(res.success){
                res.data.forEach(p => {
                    html += `<tr>
                        <td>${p.numero_pago}</td>
                        <td>${p.proveedor}</td>
                        <td>${p.fecha_pago}</td>
                        <td>$${parseFloat(p.monto).toFixed(2)}</td>
                        <td>${p.usuario}</td>
                        <td><button class="btn btn-primary btn-sm" onclick="imprimirPago(${p.id_pago})"><i class="fa fa-print"></i></button></td>
                    </tr>`;
                });
            }
            $("#pagoTable tbody").html(html);
        }, "json");
    }

    function cargarProveedores(){
        $.post("Compra_ajax.php",{accion:"listar_proveedores"}, res => {
            let html = '<option value="">Seleccione...</option>';
            res.data.forEach(p => {
                html += `<option value="${p.id_proveedores}">${p.nombre}</option>`;
            });
            $("#proveedor_pago_id").html(html);
        }, "json");
    }

    // Facturas pendientes
    $("#proveedor_pago_id").change(function(){
        const pid = $(this).val();
        if(!pid) return;

        $.post("Pago_ajax.php",{accion:"facturasProveedor", proveedor_id:pid}, res => {
            facturas = res.data;
            renderFacturas();
        }, "json");
    });

    function renderFacturas(){
        let html = "";
        facturas.forEach(f => {
            html += `
            <tr data-id="${f.id_compras}">
                <td>${f.numero_documento}</td>
                <td>${f.fecha}</td>
                <td>$${parseFloat(f.total).toFixed(2)}</td>
                <td>$${parseFloat(f.saldo).toFixed(2)}</td>
                <td><input type="number" step="0.01" class="form-control montoAplicar" style="width:130px"></td>
            </tr>`;
        });

        $("#facturasPendientesTable tbody").html(html);

        $(".montoAplicar").on("input", updateTotalAplicado);
    }

    $("#btnDistribuir").click(function(){
        let total = parseFloat($("#monto_total_pago").val());
        if(!total || total <= 0){ Swal.fire("Error","Ingrese un monto","error"); return; }

        let restante = total;

        $(".montoAplicar").each(function(){
            let saldo = parseFloat($(this).closest("tr").find("td:eq(3)").text().replace("$",""));
            let m = Math.min(saldo, restante);
            $(this).val(m.toFixed(2));
            restante -= m;
        });

        updateTotalAplicado();
    });

    function updateTotalAplicado(){
        let total = 0;
        $(".montoAplicar").each(function(){
            total += parseFloat($(this).val()) || 0;
        });
        $("#totalAplicado").text("$" + total.toFixed(2));
    }

    // Guardar pago
    $("#btnSavePago").click(function(){

        const detalles = [];

        $("#facturasPendientesTable tbody tr").each(function(){
            let compra = $(this).data("id");
            let monto = parseFloat($(this).find(".montoAplicar").val()) || 0;

            if(monto > 0){
                detalles.push({compra_id: compra, monto: monto});
            }
        });

        if(detalles.length === 0){
            Swal.fire("Error","Debe aplicar al menos un monto","error");
            return;
        }

        const payload = {
            accion: "registrar_pago",
            proveedor_id: $("#proveedor_pago_id").val(),
            usuario_id: $("#usuario_id").val(),
            fecha_pago: $("#fecha_pago").val(),
            metodo_pago: $("#metodo_pago").val(),
            nota: $("#nota_pago").val(),
            monto: parseFloat($("#totalAplicado").text().replace("$","")),
            detalle: detalles
        };

        $.ajax({
            url: "Pago_ajax.php",
            method: "POST",
            data: JSON.stringify(payload),
            contentType: "application/json",
            success: function(res){

                try { res = JSON.parse(res); } catch(e){}

                if(res.status){
                    Swal.fire({
                        icon:"success",
                        title:"Pago registrado",
                        text:res.msg,
                        confirmButtonText:"Imprimir"
                    }).then(() => {
                        window.open("Pago_imprimir.php?id="+res.pago_id,"_blank");
                    });

                    $("#pagoModal").modal("hide");
                    cargarPagos();

                } else {
                    Swal.fire("Error",res.msg,"error");
                }
            }
        });

    });

    window.imprimirPago = id => {
        window.open("Pago_imprimir.php?id="+id, "_blank");
    };

});

</script>

<?php include '../includes/footer.php'; ?>
