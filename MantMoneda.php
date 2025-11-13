<?php 
session_start();
if(!isset($_SESSION['usuario'])) {
    header("Location: index.html");
    exit();
}

require "conexion.php";
include "includes/header.php";
include "includes/sidebar.php";
?>

<div class="main-content">
    <div class="main-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col">
                    <h4 class="page-title"><i class="fas fa-coins me-2"></i>Mantenimiento de Monedas</h4>
                    <p class="page-subtitle">Registrar y administrar monedas del sistema</p>
                </div>
            </div>
        </div>
    </div>

    <div class="content-area">
        <div class="container-fluid">

            <!-- Botón abrir modal -->
            <div class="row mb-3">
                <div class="col-12 d-flex justify-content-end">
                    <button id="btnAbrirModal" class="btn btn-primary-custom">
                        <i class="fas fa-plus-circle me-2"></i> Añadir Moneda
                    </button>
                </div>
            </div>

            <!-- Modal -->
            <div class="modal fade" id="modalMoneda" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="fas fa-coins me-2"></i>Registro de Moneda</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            <form id="formMoneda">
                                <input type="hidden" name="id_monedas" id="id_monedas">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Código</label>
                                        <input type="text" name="codigo" id="codigo" class="form-control" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Nombre</label>
                                        <input type="text" name="nombre" id="nombre" class="form-control" required>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Símbolo</label>
                                        <input type="text" name="simbolo" id="simbolo" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Tasa de cambio</label>
                                        <input type="number" step="0.01" name="tasa_cambio" id="tasa_cambio" class="form-control" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Es base</label>
                                    <select name="es_base" id="es_base" class="form-control">
                                        <option value="1">Sí</option>
                                        <option value="0">No</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Estado</label>
                                    <select name="activo" id="activo" class="form-control">
                                        <option value="1">🟢 Activo</option>
                                        <option value="0">🔴 Inactivo</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary-custom w-100">
                                    <i class="fas fa-save me-2"></i>Guardar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla -->
            <div class="card card-custom fade-in">
                <div class="card-header card-header-custom">
                    <h5><i class="fas fa-list me-2"></i>Lista de Monedas</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-mantenimiento align-middle text-center">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Código</th>
                                    <th>Nombre</th>
                                    <th>Símbolo</th>
                                    <th>Tasa Cambio</th>
                                    <th>Base</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tablaMoneda"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function cargarMonedas(){
    $.get("Moneda_listar.php", function(data){
        $("#tablaMoneda").html(data);
    });
}

function editar(id, codigo, nombre, simbolo, tasa, es_base, activo){
    $("#id_monedas").val(id);
    $("#codigo").val(codigo);
    $("#nombre").val(nombre);
    $("#simbolo").val(simbolo);
    $("#tasa_cambio").val(tasa);
    $("#es_base").val(es_base);
    $("#activo").val(activo);
    let modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalMoneda'));
    modal.show();
}

function eliminar(id){
    Swal.fire({
        title: "¿Eliminar moneda?",
        text: "Esta acción no se puede deshacer.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#dc3545",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "Sí, eliminar"
    }).then((result) => {
        if (result.isConfirmed) {
            $.post("Moneda_ajax.php", { eliminar: true, id_monedas: id }, function(res){
                if(res.trim() === "ok"){
                    Swal.fire("Eliminada", "La moneda fue eliminada correctamente", "success");
                    cargarMonedas();
                } else {
                    Swal.fire("Error", res, "error");
                }
            });
        }
    });
}

$("#btnAbrirModal").click(function(){
    $("#formMoneda")[0].reset();
    $("#id_monedas").val('');
    let modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalMoneda'));
    modal.show();
});

$("#formMoneda").on("submit", function(e){
    e.preventDefault();
    $.post("Moneda_ajax.php", $(this).serialize(), function(res){
        if(res.trim() === "ok"){
            Swal.fire("✅ Éxito", "Moneda guardada correctamente", "success");
            let modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalMoneda'));
            modal.hide();
            $("#formMoneda")[0].reset();
            cargarMonedas();
        } else {
            Swal.fire("❌ Error", res, "error");
        }
    });
});

$(document).ready(function(){
    cargarMonedas();
});
</script>

<?php include "includes/footer.php"; ?>
