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
    <!-- Header de página -->
    <div class="main-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col">
                    <h4 class="page-title">
                        <i class="fas fa-building me-2"></i>Mantenimiento de Departamentos
                    </h4>
                    <p class="page-subtitle">Gestionar departamentos y categorías del sistema</p>
                </div>
                <div class="col-auto">
                    <div class="user-info">
                        <div class="user-avatar">
                            <?php echo strtoupper(substr($_SESSION['usuario'], 0, 1)); ?>
                        </div>
                        <div class="user-details">
                            <div class="username"><?php echo $_SESSION['usuario']; ?></div>
                            <div class="role">Administrador</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="content-area">
        <div class="container-fluid">
            <!-- Botón para abrir el formulario en modal -->
            <div class="row mb-3">
                <div class="col-12 d-flex justify-content-end">
                    <button id="btnAbrirModal" type="button" class="btn btn-primary-custom">
                        <i class="fas fa-plus-circle me-2"></i>Añadir Departamento
                    </button>
                </div>
            </div>

            <!-- Modal: Formulario de Departamentos (oculto por defecto) -->
            <div class="modal fade" id="modalDepartamentos" tabindex="-1" aria-labelledby="modalDepartamentosLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalDepartamentosLabel"><i class="fas fa-plus-circle me-2"></i>Registro de Departamentos</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            <form id="formDepartamentos" class="form-mantenimiento">
                                <input type="hidden" name="id_departamentos" id="id_departamentos">

                                <div class="row mb-4">
                                    <div class="col-md-8">
                                        <label class="form-label text-required">Nombre del Departamento</label>
                                        <input type="text" name="nombre" id="nombre" class="form-control form-control-custom" 
                                               pattern="[A-Za-z0-9ÁÉÍÓÚáéíóúÑñ\s]+" 
                                               title="Solo letras, números y espacios" 
                                               maxlength="80" 
                                               placeholder="Ej: Ventas, Tecnología, Administración" required>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <label class="form-label text-required">Estado</label>
                                        <select name="activo" id="activo" class="form-control form-control-custom" required>
                                            <option value="1">🟢 Activo</option>
                                            <option value="0">🔴 Inactivo</option>
                                        </select>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary-custom btn-custom w-100">
                                    <i class="fas fa-save me-2"></i>Guardar Departamento
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tarjeta de Listado -->
            <div class="row justify-content-center mt-4">
                <div class="col-12">
                    <div class="card card-custom fade-in">
                        <div class="card-header card-header-custom">
                            <h5 class="mb-0">
                                <i class="fas fa-list me-2"></i>Lista de Departamentos
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-mantenimiento">
                                    <thead>
                                        <tr>
                                            <th width="10%">ID</th>
                                            <th width="60%">Nombre</th>
                                            <th width="15%">Estado</th>
                                            <th width="15%">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tablaDepartamentos"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function cargarDepartamentos(){
    $.ajax({
        url: "Departamento_listar.php",
        type: "GET",
        success: function(data){
            $("#tablaDepartamentos").html(data);
        }
    });
}

function editar(id, nombre, activo){
    $("#id_departamentos").val(id);
    $("#nombre").val(nombre);
    $("#activo").val(activo);
    // Abrir modal con el formulario para editar
    var modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalDepartamentos'));
    modal.show();
}

function eliminarDepartamento(id) {
    Swal.fire({
        title: "¿Eliminar departamento?",
        text: "Esta acción no se puede deshacer",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#dc3545",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "Sí, eliminar",
        cancelButtonText: "Cancelar"
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "Departamento_ajax.php",
                type: "POST",
                data: { accion: "eliminar", id: id },
                success: function(res) {
                    if (res.trim() === "ok") {
                        Swal.fire({
                            title: "✅ Eliminado",
                            text: "El departamento ha sido eliminado correctamente",
                            icon: "success",
                            confirmButtonColor: "#004aad"
                        });
                        cargarDepartamentos();
                    } else {
                        Swal.fire({
                            title: "❌ Error",
                            text: "No se pudo eliminar. Es posible que esté asociado a otros registros.",
                            icon: "error",
                            confirmButtonColor: "#dc3545"
                        });
                    }
                }
            });
        }
    });
}

// Abrir modal para añadir nuevo departamento y limpiar el formulario
$(document).on('click', '#btnAbrirModal', function(){
    $("#formDepartamentos")[0].reset();
    $("#id_departamentos").val('');
    var modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalDepartamentos'));
    modal.show();
});

$("#formDepartamentos").on("submit", function(e){
    e.preventDefault();

    // Mostrar loading
    const submitBtn = $(this).find('button[type="submit"]');
    const originalText = submitBtn.html();
    submitBtn.html('<i class="fas fa-spinner fa-spin me-2"></i>Guardando...');
    submitBtn.prop('disabled', true);

    $.ajax({
        url: "Departamento_ajax.php",
        type: "POST",
        data: $(this).serialize(),
        success: function(res){
            // Restaurar botón
            submitBtn.html(originalText);
            submitBtn.prop('disabled', false);

            if(res == "ok"){
                Swal.fire({
                    title: "✅ Éxito",
                    text: "Departamento guardado correctamente",
                    icon: "success",
                    confirmButtonColor: "#004aad"
                });
                // Cerrar modal y limpiar formulario
                var modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalDepartamentos'));
                modal.hide();
                $("#formDepartamentos")[0].reset();
                $("#id_departamentos").val("");
                cargarDepartamentos();
            } else {
                Swal.fire({
                    title: "❌ Error",
                    text: res,
                    icon: "error",
                    confirmButtonColor: "#dc3545"
                });
            }
        },
        error: function(){
            // Restaurar botón en caso de error
            submitBtn.html(originalText);
            submitBtn.prop('disabled', false);
            Swal.fire({
                title: "❌ Error de conexión",
                text: "No se pudo conectar con el servidor",
                icon: "error",
                confirmButtonColor: "#dc3545"
            });
        }
    });
});

// Cargar departamentos al iniciar
$(document).ready(function(){
    cargarDepartamentos();
});
</script>

<?php include "includes/footer.php"; ?>