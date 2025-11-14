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
                    <h4 class="page-title"><i class="fas fa-handshake me-2"></i>Mantenimiento de Condiciones de Pago</h4>
                    <p class="page-subtitle">Gestione las condiciones de pago disponibles</p>
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
                        <i class="fas fa-plus-circle me-2"></i> Añadir Condición
                    </button>
                </div>
            </div>

            <!-- Modal -->
            <div class="modal fade" id="modalCondicion" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-md modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="fas fa-handshake me-2"></i>Registro de Condición de Pago</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            <form id="formCondicionPago">
                                <input type="hidden" name="id_condiciones_pago" id="id_condiciones_pago">

                                <div class="mb-3">
                                    <label class="form-label">Nombre</label>
                                    <input type="text" name="nombre" id="nombre" class="form-control" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Días de Plazo</label>
                                    <input type="number" step="1" name="dias_plazo" id="dias_plazo" class="form-control" min="0" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Activo</label>
                                    <select name="activo" id="activo" class="form-control" required>
                                        <option value="1">Sí</option>
                                        <option value="0">No</option>
                                    </select>
                                </div>

                                <button type="submit" class="btn btn-primary-custom w-100">
                                    <i class="fas fa-save me-2"></i>Guardar Condición
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla -->
            <div class="card card-custom fade-in mt-4">
                <div class="card-header card-header-custom">
                    <h5><i class="fas fa-list me-2"></i>Lista de Condiciones de Pago</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-mantenimiento align-middle text-center">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Días de Plazo</th>
                                    <th>Activo</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tablaCondicionPago"></tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
function cargarCondiciones(){
    $.get("CondicionPago_listar.php", function(data){
        $("#tablaCondicionPago").html(data);
    });
}

function editar(id, nombre, dias_plazo, activo){
    $("#id_condiciones_pago").val(id);
    $("#nombre").val(nombre);
    $("#dias_plazo").val(dias_plazo);
    $("#activo").val(activo);
    let modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalCondicion'));
    modal.show();
}

function eliminar(id){
    Swal.fire({
        title: "¿Eliminar condición?",
        text: "Esta acción no se puede deshacer.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#dc3545",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "Sí, eliminar"
    }).then((result) => {
        if (result.isConfirmed) {
            $.post("CondicionPago_ajax.php", { eliminar: true, id_condiciones_pago: id }, function(res){
                if(res.trim() === "ok"){
                    Swal.fire("Eliminado", "La condición fue eliminada correctamente", "success");
                    cargarCondiciones();
                } else {
                    Swal.fire("Error", res, "error");
                }
            });
        }
    });
}

$("#btnAbrirModal").click(function(){
    $("#formCondicionPago")[0].reset();
    $("#id_condiciones_pago").val('');
    let modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalCondicion'));
    modal.show();
});

$("#formCondicionPago").on("submit", function(e){
    e.preventDefault();
    $.post("CondicionPago_ajax.php", $(this).serialize(), function(res){
        if(res.trim() === "ok"){
            Swal.fire("✅ Éxito", "Condición guardada correctamente", "success");
            let modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalCondicion'));
            modal.hide();
            $("#formCondicionPago")[0].reset();
            cargarCondiciones();
        } else {
            Swal.fire("❌ Error", res, "error");
        }
    });
});

$(document).ready(function(){
    cargarCondiciones();
});
</script>

<?php include "includes/footer.php"; ?>
