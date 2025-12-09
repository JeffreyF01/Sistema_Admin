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
                    <h4 class="page-title"><i class="fas fa-warehouse me-2"></i>Mantenimiento de Almacenes</h4>
                    <p class="page-subtitle">Registrar y administrar almacenes del sistema</p>
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
                        <i class="fas fa-plus-circle me-2"></i> Añadir Almacén
                    </button>
                </div>
            </div>

            <!-- Modal -->
            <div class="modal fade" id="modalAlmacen" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="fas fa-warehouse me-2"></i>Registro de Almacén</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            <form id="formAlmacen">
                                <input type="hidden" name="id_almacen" id="id_almacen">
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Código <span class="text-danger">*</span></label>
                                        <input type="text" name="codigo" id="codigo" class="form-control" 
                                               pattern="[A-Za-z0-9\-_]+" 
                                               title="Solo alfanumérico, guiones" 
                                               maxlength="40" 
                                               style="text-transform:uppercase" 
                                               placeholder="ALM-001" required>
                                    </div>
                                    <div class="col-md-8">
                                        <label class="form-label">Nombre <span class="text-danger">*</span></label>
                                        <input type="text" name="nombre" id="nombre" class="form-control" 
                                               maxlength="120" 
                                               placeholder="Almacén Principal" required>
                                    </div>
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
                    <h5><i class="fas fa-list me-2"></i>Lista de Almacenes</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-mantenimiento align-middle text-center">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Código</th>
                                    <th>Nombre</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tablaAlmacen"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function cargarAlmacenes(){
    $.get("Almacen_listar.php", function(data){
        $("#tablaAlmacen").html(data);
    });
}

function editar(id, codigo, nombre, activo){
    $("#id_almacen").val(id);
    $("#codigo").val(codigo);
    $("#nombre").val(nombre);
    $("#activo").val(activo);
    let modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalAlmacen'));
    modal.show();
}

function eliminar(id){
    Swal.fire({
        title: "¿Eliminar almacén?",
        text: "Esta acción no se puede deshacer.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#dc3545",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "Sí, eliminar"
    }).then((result) => {
        if (result.isConfirmed) {
            $.post("Almacen_ajax.php", { eliminar: true, id_almacen: id }, function(res){
                if(res.trim() === "ok"){
                    Swal.fire("Eliminado", "El almacén fue eliminado correctamente", "success");
                    cargarAlmacenes();
                } else {
                    Swal.fire("Error", res, "error");
                }
            });
        }
    });
}

$("#btnAbrirModal").click(function(){
    $("#formAlmacen")[0].reset();
    $("#id_almacen").val('');
    let modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalAlmacen'));
    modal.show();
});

$("#formAlmacen").on("submit", function(e){
    e.preventDefault();
    $.post("Almacen_ajax.php", $(this).serialize(), function(res){
        if(res.trim() === "ok"){
            Swal.fire("✅ Éxito", "Almacén guardado correctamente", "success");
            let modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalAlmacen'));
            modal.hide();
            $("#formAlmacen")[0].reset();
            cargarAlmacenes();
        } else {
            Swal.fire("❌ Error", res, "error");
        }
    });
});

$(document).ready(function(){
    cargarAlmacenes();
});
</script>

<?php include "includes/footer.php"; ?>
