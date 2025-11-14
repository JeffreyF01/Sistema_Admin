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
                    <h4 class="page-title"><i class="fas fa-user-group me-2"></i>Mantenimiento de Clientes</h4>
                    <p class="page-subtitle">Registrar y administrar clientes</p>
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
                        <i class="fas fa-plus-circle me-2"></i> Añadir Cliente
                    </button>
                </div>
            </div>

            <!-- Modal -->
            <div class="modal fade" id="modalCliente" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i>Registro de Cliente</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            <form id="formCliente">
                                <input type="hidden" name="id_clientes" id="id_clientes">

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Nombre</label>
                                        <input type="text" name="nombre" id="nombre" class="form-control" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Documento (Cédula / RNC)</label>
                                        <input type="text" name="doc_identidad" id="doc_identidad" class="form-control">
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
                                    <i class="fas fa-save me-2"></i>Guardar Cliente
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla -->
            <div class="card card-custom fade-in mt-4">
                <div class="card-header card-header-custom">
                    <h5><i class="fas fa-list me-2"></i>Lista de Clientes</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-mantenimiento align-middle text-center">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Documento</th>
                                    <th>Email</th>
                                    <th>Teléfono</th>
                                    <th>Dirección</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tablaCliente"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function cargarClientes(){
    $.get("Cliente_listar.php", function(data){
        $("#tablaCliente").html(data);
    });
}

function editar(id){
    $.get("Cliente_ajax.php", { obtener: id }, function(res){
        let data;
        try {
            data = JSON.parse(res);
        } catch(e){
            Swal.fire("Error", "Respuesta inválida del servidor", "error");
            return;
        }
        if(data.error){
            Swal.fire("Error", data.error, "error");
            return;
        }

        $("#id_clientes").val(data.id_clientes);
        $("#nombre").val(data.nombre);
        $("#doc_identidad").val(data.doc_identidad);
        $("#email").val(data.email);
        $("#telefono").val(data.telefono);
        $("#direccion").val(data.direccion);
        $("#activo").val(data.activo);

        let modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalCliente'));
        modal.show();
    });
}

function eliminar(id){
    Swal.fire({
        title: "¿Eliminar cliente?",
        text: "Esta acción no se puede deshacer.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#dc3545",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "Sí, eliminar"
    }).then((result) => {
        if (result.isConfirmed) {
            $.post("Cliente_ajax.php", { eliminar: true, id_clientes: id }, function(res){
                if(res.trim() === "ok"){
                    Swal.fire("Eliminado", "El cliente fue eliminado correctamente", "success");
                    cargarClientes();
                } else {
                    Swal.fire("Error", res, "error");
                }
            });
        }
    });
}

$("#btnAbrirModal").click(function(){
    $("#formCliente")[0].reset();
    $("#id_clientes").val('');
    let modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalCliente'));
    modal.show();
});

$("#formCliente").on("submit", function(e){
    e.preventDefault();
    $.post("Cliente_ajax.php", $(this).serialize(), function(res){
        if(res.trim() === "ok"){
            Swal.fire("✅ Éxito", "Cliente guardado correctamente", "success");
            let modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalCliente'));
            modal.hide();
            $("#formCliente")[0].reset();
            cargarClientes();
        } else {
            Swal.fire("❌ Error", res, "error");
        }
    });
});

$(document).ready(function(){
    cargarClientes();
});
</script>

<?php include "includes/footer.php"; ?>
