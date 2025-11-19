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
                    <h4 class="page-title"><i class="fas fa-truck-field me-2"></i>Mantenimiento de Proveedores</h4>
                    <p class="page-subtitle">Registrar y administrar proveedores</p>
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
                        <i class="fas fa-plus-circle me-2"></i> Añadir Proveedor
                    </button>
                </div>
            </div>

            <!-- Modal -->
            <div class="modal fade" id="modalProveedor" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="fas fa-user-tie me-2"></i>Registro de Proveedor</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">
                            <form id="formProveedor">
                                <input type="hidden" name="id_proveedores" id="id_proveedores">

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Nombre / Razón social</label>
                                        <input type="text" name="nombre" id="nombre" class="form-control" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">RNC</label>
                                        <input type="text" name="rnc" id="rnc" class="form-control">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Email</label>
                                        <input type="email" name="email" id="email" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Teléfono</label>
                                        <input type="text" name="telefono" id="telefono" class="form-control">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Dirección</label>
                                    <input type="text" name="direccion" id="direccion" class="form-control">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Estado</label>
                                    <select name="activo" id="activo" class="form-control">
                                        <option value="1">🟢 Activo</option>
                                        <option value="0">🔴 Inactivo</option>
                                    </select>
                                </div>

                                <button type="submit" class="btn btn-primary-custom w-100">
                                    <i class="fas fa-save me-2"></i>Guardar Proveedor
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla -->
            <div class="card card-custom fade-in mt-4">
                <div class="card-header card-header-custom">
                    <h5><i class="fas fa-list me-2"></i>Lista de Proveedores</h5>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-mantenimiento text-center align-middle">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>RNC</th>
                                    <th>Email</th>
                                    <th>Teléfono</th>
                                    <th>Dirección</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tablaProveedor"></tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
function cargarProveedores(){
    $.get("Proveedor_listar.php", function(data){
        $("#tablaProveedor").html(data);
    });
}

function editar(id){
    $.get("Proveedor_ajax.php", {obtener:id}, function(res){
        let data = JSON.parse(res);

        if(data.error){
            Swal.fire("Error", data.error, "error");
            return;
        }

        $("#id_proveedores").val(data.id_proveedores);
        $("#nombre").val(data.nombre);
        $("#rnc").val(data.rnc);
        $("#email").val(data.email);
        $("#telefono").val(data.telefono);
        $("#direccion").val(data.direccion);
        $("#activo").val(data.activo);

        let modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalProveedor'));
        modal.show();
    });
}

function eliminar(id){
    Swal.fire({
        title: "¿Eliminar proveedor?",
        text: "Esta acción no se puede deshacer.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#dc3545",
        confirmButtonText: "Sí, eliminar"
    }).then(res => {
        if(res.isConfirmed){
            $.post("Proveedor_ajax.php", { eliminar:true, id_proveedores:id }, function(resp){
                if(resp.trim() === "ok"){
                    Swal.fire("Eliminado", "Proveedor eliminado correctamente", "success");
                    cargarProveedores();
                } else {
                    Swal.fire("Error", resp, "error");
                }
            });
        }
    });
}

$("#btnAbrirModal").click(function(){
    $("#formProveedor")[0].reset();
    $("#id_proveedores").val('');
    let modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalProveedor'));
    modal.show();
});

$("#formProveedor").on("submit", function(e){
    e.preventDefault();
    $.post("Proveedor_ajax.php", $(this).serialize(), function(res){
        if(res.trim() === "ok"){
            Swal.fire("Éxito", "Proveedor guardado correctamente", "success");
            bootstrap.Modal.getOrCreateInstance(document.getElementById('modalProveedor')).hide();
            $("#formProveedor")[0].reset();
            cargarProveedores();
        } else {
            Swal.fire("Error", res, "error");
        }
    });
});

$(document).ready(function(){
    cargarProveedores();
});
</script>

<?php include "includes/footer.php"; ?>
