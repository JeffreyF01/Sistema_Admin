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
                    <h4 class="page-title"><i class="fas fa-user-shield me-2"></i>Mantenimiento de Roles</h4>
                    <p class="page-subtitle">Registrar y administrar roles del sistema</p>
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
                        <i class="fas fa-plus-circle me-2"></i> Añadir Rol
                    </button>
                </div>
            </div>

            <!-- Modal -->
            <div class="modal fade" id="modalRol" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="fas fa-user-shield me-2"></i>Registro de Rol</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            <form id="formRol">
                                <input type="hidden" name="id_roles" id="id_roles">

                                <div class="mb-3">
                                    <label class="form-label">Nombre del Rol</label>
                                    <input type="text" name="nombre" id="nombre" class="form-control" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Descripción</label>
                                    <textarea name="descripcion" id="descripcion" class="form-control" rows="3" required></textarea>
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
                    <h5><i class="fas fa-list me-2"></i>Lista de Roles</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-mantenimiento align-middle text-center">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Descripción</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tablaRoles"></tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
function cargarRoles(){
    $.get("Rol_listar.php", function(data){
        $("#tablaRoles").html(data);
    });
}

function editar(id, nombre, descripcion, activo){
    $("#id_roles").val(id);
    $("#nombre").val(nombre);
    $("#descripcion").val(descripcion);
    $("#activo").val(activo);
    let modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalRol'));
    modal.show();
}

function eliminar(id){
    Swal.fire({
        title: "¿Eliminar rol?",
        text: "Esta acción no se puede deshacer.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#dc3545",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "Sí, eliminar"
    }).then((result) => {
        if (result.isConfirmed) {
            $.post("Rol_ajax.php", { eliminar: true, id_roles: id }, function(res){
                if(res.trim() === "ok"){
                    Swal.fire("Eliminado", "El rol fue eliminado correctamente", "success");
                    cargarRoles();
                } else {
                    Swal.fire("Error", res, "error");
                }
            });
        }
    });
}

$("#btnAbrirModal").click(function(){
    $("#formRol")[0].reset();
    $("#id_roles").val('');
    let modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalRol'));
    modal.show();
});

$("#formRol").on("submit", function(e){
    e.preventDefault();
    $.post("Rol_ajax.php", $(this).serialize(), function(res){
        if(res.trim() === "ok"){
            Swal.fire("✅ Éxito", "Rol guardado correctamente", "success");
            let modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalRol'));
            modal.hide();
            $("#formRol")[0].reset();
            cargarRoles();
        } else {
            Swal.fire("❌ Error", res, "error");
        }
    });
});

$(document).ready(function(){
    cargarRoles();
});
</script>

<?php include "includes/footer.php"; ?>
