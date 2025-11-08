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
                    <h4 class="page-title">
                        <i class="fas fa-arrows-rotate me-2"></i>Mantenimiento de Tipos de Movimiento
                    </h4>
                    <p class="page-subtitle">Gestionar clasificación de movimientos de inventario</p>
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
            <!-- Botón para abrir modal de Tipos de Movimiento -->
            <div class="row mb-3">
                <div class="col-12 d-flex justify-content-end">
                    <button id="btnAbrirModalMovimiento" type="button" class="btn btn-primary-custom">
                        <i class="fas fa-plus-circle me-2"></i>Añadir Tipo de Movimiento
                    </button>
                </div>
            </div>

            <!-- Modal: Formulario de Tipos de Movimiento -->
            <div class="modal fade" id="modalMovimiento" tabindex="-1" aria-labelledby="modalMovimientoLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalMovimientoLabel"><i class="fas fa-plus-circle me-2"></i>Registro de Tipos de Movimiento</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            <form id="formMovimientos" class="form-mantenimiento">
                                <input type="hidden" name="id_tipos_movimiento" id="id_tipos_movimiento">

                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <label class="form-label text-required">Nombre del Movimiento</label>
                                        <input type="text" name="nombre" id="nombre" class="form-control form-control-custom" required 
                                               placeholder="Ej: Entrada por Compra, Salida por Venta">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label text-required">Clase</label>
                                        <select name="clase" id="clase" class="form-control form-control-custom" required>
                                            <option value="">Seleccione la clase...</option>
                                            <option value="ENTRADA">🟢 ENTRADA</option>
                                            <option value="SALIDA">🔴 SALIDA</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <div class="col-12">
                                        <label class="form-label">Descripción</label>
                                        <textarea name="descripcion" id="descripcion" class="form-control form-control-custom" rows="3" 
                                                  placeholder="Descripción detallada del tipo de movimiento..."></textarea>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4 mb-4">
                                        <label class="form-label text-required">Estado</label>
                                        <select name="activo" id="activo" class="form-control form-control-custom" required>
                                            <option value="1">🟢 Activo</option>
                                            <option value="0">🔴 Inactivo</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end mt-2">
                                    <button type="submit" class="btn btn-primary-custom btn-custom">
                                        <i class="fas fa-save me-2"></i>Guardar Tipo de Movimiento
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row justify-content-center mt-4">
                <div class="col-12">
                    <div class="card card-custom fade-in">
                        <div class="card-header card-header-custom">
                            <h5 class="mb-0">
                                <i class="fas fa-list me-2"></i>Lista de Tipos de Movimiento
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-mantenimiento">
                                    <thead>
                                        <tr>
                                            <th width="8%">ID</th>
                                            <th width="20%">Nombre</th>
                                            <th width="12%">Clase</th>
                                            <th width="40%">Descripción</th>
                                            <th width="10%">Estado</th>
                                            <th width="10%">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tablaMovimientos"></tbody>
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
function cargarMovimientos(){
    $.ajax({
        url: "TiposMov_listar.php",
        type: "GET",
        success: function(data){
            $("#tablaMovimientos").html(data);
        }
    });
}

function editarMovimiento(id, nombre, clase, descripcion, activo){
    $("#id_tipos_movimiento").val(id);
    $("#nombre").val(nombre);
    $("#clase").val(clase);
    $("#descripcion").val(descripcion);
    $("#activo").val(activo);

    // Mostrar modal para editar
    var modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalMovimiento'));
    modal.show();
}

$("#formMovimientos").on("submit", function(e){
    e.preventDefault();

    const submitBtn = $(this).find('button[type="submit"]');
    const originalText = submitBtn.html();
    submitBtn.html('<i class="fas fa-spinner fa-spin me-2"></i>Guardando...');
    submitBtn.prop('disabled', true);

    $.ajax({
        url: "TiposMov_ajax.php",
        type: "POST",
        data: $(this).serialize(),
        success: function(res){
            submitBtn.html(originalText);
            submitBtn.prop('disabled', false);

            if(res.trim() == "ok"){
                Swal.fire({
                    title: "✅ Éxito",
                    text: "Tipo de movimiento registrado correctamente",
                    icon: "success",
                    confirmButtonColor: "#004aad"
                });
                // Cerrar modal y limpiar formulario
                var modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalMovimiento'));
                modal.hide();
                $("#formMovimientos")[0].reset();
                $("#id_tipos_movimiento").val('');
                cargarMovimientos();
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

$(document).ready(function(){
    cargarMovimientos();
});
</script>

<script>
// Abrir modal para añadir nuevo
$(document).on('click', '#btnAbrirModalMovimiento', function(){
    $("#formMovimientos")[0].reset();
    $("#id_tipos_movimiento").val('');
    var modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalMovimiento'));
    modal.show();
});
</script>

<?php include "includes/footer.php"; ?>