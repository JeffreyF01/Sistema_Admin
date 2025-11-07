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
            <!-- Tarjeta de Formulario -->
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card card-custom fade-in">
                        <div class="card-header card-header-custom">
                            <h5 class="mb-0">
                                <i class="fas fa-plus-circle me-2"></i>Registro de Departamentos
                            </h5>
                        </div>
                        <div class="card-body">
                            <form id="formDepartamentos" class="form-mantenimiento">
                                <input type="hidden" name="id_departamentos" id="id_departamentos">

                                <div class="row mb-4">
                                    <div class="col-md-8">
                                        <label class="form-label text-required">Nombre del Departamento</label>
                                        <input type="text" name="nombre" id="nombre" class="form-control form-control-custom" required 
                                               placeholder="Ej: Ventas, Tecnología, Administración">
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
    
    // Scroll suave al formulario
    $('html, body').animate({
        scrollTop: $(".card-custom").first().offset().top - 20
    }, 500);
}

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