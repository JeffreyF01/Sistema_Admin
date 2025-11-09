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
                        <i class="fas fa-location-dot me-2"></i>Mantenimiento de Ubicaciones
                    </h4>
                    <p class="page-subtitle">Registrar y administrar ubicaciones internas de almacenamiento</p>
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

    <!-- Contenido principal -->
    <div class="content-area">
        <div class="container-fluid">
            <!-- Botón para abrir modal -->
            <div class="row mb-3">
                <div class="col-12 d-flex justify-content-end">
                    <button id="btnAbrirModalUbicacion" type="button" class="btn btn-primary-custom">
                        <i class="fas fa-plus-circle me-2"></i>Añadir Ubicación
                    </button>
                </div>
            </div>

            <!-- Modal de Ubicación -->
            <div class="modal fade" id="modalUbicacion" tabindex="-1" aria-labelledby="modalUbicacionLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalUbicacionLabel"><i class="fas fa-plus-circle me-2"></i>Registro de Ubicación</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            <form id="formUbicacion" class="form-mantenimiento">
                                <input type="hidden" name="id_ubicaciones" id="id_ubicaciones">

                                <div class="row">
                                    <div class="col-md-4 mb-4">
                                        <label class="form-label text-required">Código de Ubicación</label>
                                        <input type="text" name="codigo" id="codigo" class="form-control form-control-custom" required placeholder="Ej: UBI-001">
                                    </div>

                                    <div class="col-md-8 mb-4">
                                        <label class="form-label text-required">Nombre de la Ubicación</label>
                                        <input type="text" name="nombre" id="nombre" class="form-control form-control-custom" required placeholder="Ej: Estantería A - Nivel 1">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-4">
                                        <label class="form-label text-required">Almacén</label>
                                        <select name="id_almacen" id="id_almacen" class="form-control form-control-custom" required>
                                            <option value="">Seleccione un almacén...</option>
                                            <?php
                                            $sql = $conexion->query("SELECT id_almacen, nombre FROM almacen WHERE activo = 1 ORDER BY nombre");
                                            while($a = $sql->fetch_assoc()){
                                                echo "<option value='".$a['id_almacen']."'>".$a['nombre']."</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>

                                    <div class="col-md-6 mb-4">
                                        <label class="form-label text-required">Estado</label>
                                        <select name="activo" id="activo" class="form-control form-control-custom" required>
                                            <option value="1">🟢 Activo</option>
                                            <option value="0">🔴 Inactivo</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end mt-2">
                                    <button type="submit" class="btn btn-primary-custom btn-custom">
                                        <i class="fas fa-save me-2"></i>Guardar Ubicación
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla de Ubicaciones -->
            <div class="row justify-content-center mt-4">
                <div class="col-12">
                    <div class="card card-custom fade-in">
                        <div class="card-header card-header-custom">
                            <h5 class="mb-0">
                                <i class="fas fa-list me-2"></i>Lista de Ubicaciones
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-mantenimiento">
                                    <thead>
                                        <tr>
                                            <th width="8%">ID</th>
                                            <th width="20%">Código</th>
                                            <th width="30%">Nombre</th>
                                            <th width="20%">Almacén</th>
                                            <th width="12%">Estado</th>
                                            <th width="10%">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tablaUbicaciones"></tbody>
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
function cargarUbicaciones(){
    $.ajax({
        url: "Ubicacion_listar.php",
        type: "GET",
        success: function(data){
            $("#tablaUbicaciones").html(data);
        }
    });
}

// Abrir modal NUEVO
$(document).on('click', '#btnAbrirModalUbicacion', function(){
    $("#formUbicacion")[0].reset();
    $("#id_ubicaciones").val('');
    $("#modalUbicacionLabel").html('<i class="fas fa-plus-circle me-2"></i>Registro de Ubicación');
    var modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalUbicacion'));
    modal.show();
});

// Abrir modal EDITAR (usa data-atributos del botón)
$(document).on("click", ".btn-editar", function(){
    $("#id_ubicaciones").val($(this).data("id"));
    $("#codigo").val($(this).data("codigo"));
    $("#nombre").val($(this).data("nombre"));
    $("#id_almacen").val($(this).data("almacen"));
    $("#activo").val($(this).data("activo"));
    $("#modalUbicacionLabel").html('<i class="fas fa-edit me-2"></i>Editar Ubicación');
    let modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalUbicacion'));
    modal.show();
});

// Guardar / actualizar
$("#formUbicacion").on("submit", function(e){
    e.preventDefault();
    const submitBtn = $(this).find('button[type="submit"]');
    const originalText = submitBtn.html();
    submitBtn.html('<i class="fas fa-spinner fa-spin me-2"></i>Guardando...').prop('disabled', true);

    $.ajax({
        url: "Ubicacion_ajax.php",
        type: "POST",
        data: $(this).serialize(),
        success: function(res){
            submitBtn.html(originalText).prop('disabled', false);
            if(res.trim() === "ok"){
                Swal.fire({
                    title: "✅ Éxito",
                    text: "Ubicación guardada correctamente",
                    icon: "success",
                    confirmButtonColor: "#004aad"
                });
                let modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalUbicacion'));
                modal.hide();
                $("#formUbicacion")[0].reset();
                $("#id_ubicaciones").val('');
                cargarUbicaciones();
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
            submitBtn.html(originalText).prop('disabled', false);
            Swal.fire({
                title: "❌ Error de conexión",
                text: "No se pudo conectar con el servidor",
                icon: "error",
                confirmButtonColor: "#dc3545"
            });
        }
    });
});

// Eliminar con confirmación
function eliminar(id){
    Swal.fire({
        title: "¿Eliminar ubicación?",
        text: "Esta acción no se puede deshacer",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#dc3545",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "Sí, eliminar"
    }).then((result) => {
        if(result.isConfirmed){
            $.post("Ubicacion_ajax.php", { eliminar: id }, function(res){
                if(res.trim() === "ok"){
                    Swal.fire({
                        title: "✅ Eliminado",
                        text: "Ubicación eliminada correctamente",
                        icon: "success",
                        confirmButtonColor: "#004aad"
                    });
                    cargarUbicaciones();
                } else {
                    Swal.fire({
                        title: "❌ Error",
                        text: res,
                        icon: "error",
                        confirmButtonColor: "#dc3545"
                    });
                }
            });
        }
    });
}

// Cargar ubicaciones al iniciar
$(document).ready(function(){
    cargarUbicaciones();
});
</script>

<?php include "includes/footer.php"; ?>
