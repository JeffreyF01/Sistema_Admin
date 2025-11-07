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
            <!-- Tarjeta de Formulario -->
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="card card-custom fade-in">
                        <div class="card-header card-header-custom">
                            <h5 class="mb-0">
                                <i class="fas fa-plus-circle me-2"></i>Registro de Tipos de Movimiento
                            </h5>
                        </div>
                        <div class="card-body">
                            <form id="formMovimientos" class="form-mantenimiento">
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <label class="form-label text-required">Nombre del Movimiento</label>
                                        <input type="text" name="nombre" class="form-control form-control-custom" required 
                                               placeholder="Ej: Entrada por Compra, Salida por Venta">
                                  
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label text-required">Clase</label>
                                        <select name="clase" class="form-control form-control-custom" required>
                                            <option value="">Seleccione la clase...</option>
                                            <option value="ENTRADA">🟢 ENTRADA</option>
                                            <option value="SALIDA">🔴 SALIDA</option>
                                        </select>
                                     
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <div class="col-12">
                                        <label class="form-label">Descripción</label>
                                        <textarea name="descripcion" class="form-control form-control-custom" rows="3" 
                                                  placeholder="Descripción detallada del tipo de movimiento..."></textarea>
                                       
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4 mb-4">
                                        <label class="form-label text-required">Estado</label>
                                        <select name="activo" class="form-control form-control-custom" required>
                                            <option value="1">🟢 Activo</option>
                                            <option value="0">🔴 Inactivo</option>
                                        </select>
                                      
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary-custom btn-custom w-100">
                                    <i class="fas fa-save me-2"></i>Guardar Tipo de Movimiento
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
    // Llenar formulario con datos para editar
    $("input[name='nombre']").val(nombre);
    $("select[name='clase']").val(clase);
    $("textarea[name='descripcion']").val(descripcion);
    $("select[name='activo']").val(activo);
    
    // Scroll suave al formulario
    $('html, body').animate({
        scrollTop: $(".card-custom").first().offset().top - 20
    }, 500);
}

$("#formMovimientos").on("submit", function(e){
    e.preventDefault();

    // Mostrar loading
    const submitBtn = $(this).find('button[type="submit"]');
    const originalText = submitBtn.html();
    submitBtn.html('<i class="fas fa-spinner fa-spin me-2"></i>Guardando...');
    submitBtn.prop('disabled', true);

    $.ajax({
        url: "TiposMov_ajax.php",
        type: "POST",
        data: $(this).serialize(),
        success: function(res){
            // Restaurar botón
            submitBtn.html(originalText);
            submitBtn.prop('disabled', false);

            if(res == "ok"){
                Swal.fire({
                    title: "✅ Éxito",
                    text: "Tipo de movimiento registrado correctamente",
                    icon: "success",
                    confirmButtonColor: "#004aad"
                });
                $("#formMovimientos")[0].reset();
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

// Cargar movimientos al iniciar
$(document).ready(function(){
    cargarMovimientos();
});
</script>

<?php include "includes/footer.php"; ?>