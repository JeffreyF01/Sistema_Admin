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
                        <i class="fas fa-people-group me-2"></i>Mantenimiento de Grupos
                    </h4>
                    <p class="page-subtitle">Gestionar grupos y subcategorías de productos</p>
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
                                <i class="fas fa-plus-circle me-2"></i>Registro de Grupos
                            </h5>
                        </div>
                        <div class="card-body">
                            <form id="formGrupos" class="form-mantenimiento">
                                <div class="row">
                                    <div class="col-md-6 mb-4">
                                        <label class="form-label text-required">Departamento</label>
                                        <select name="departamento_id" class="form-control form-control-custom" required>
                                            <option value="">Seleccione un departamento...</option>
                                            <?php
                                            $sql = $conexion->query("SELECT id_departamentos, nombre FROM departamento WHERE activo = 1 ORDER BY nombre");
                                            while($d = $sql->fetch_assoc()){
                                                echo "<option value='".$d['id_departamentos']."'>".$d['nombre']."</option>";
                                            }
                                            ?>
                                        </select>
                                       
                                    </div>

                                    <div class="col-md-6 mb-4">
                                        <label class="form-label text-required">Nombre del Grupo</label>
                                        <input type="text" name="nombre" class="form-control form-control-custom" required 
                                               placeholder="Ej: Laptops, Smartphones, Accesorios">
                                     
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
                                    <i class="fas fa-save me-2"></i>Guardar Grupo
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
                                <i class="fas fa-list me-2"></i>Lista de Grupos
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-mantenimiento">
                                    <thead>
                                        <tr>
                                            <th width="10%">ID</th>
                                            <th width="35%">Departamento</th>
                                            <th width="35%">Grupo</th>
                                            <th width="20%">Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tablaGrupos"></tbody>
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
function cargarGrupos(){
    $.ajax({
        url: "Grupo_listar.php",
        type: "GET",
        success: function(data){
            $("#tablaGrupos").html(data);
        }
    });
}

$("#formGrupos").on("submit", function(e){
    e.preventDefault();

    // Mostrar loading
    const submitBtn = $(this).find('button[type="submit"]');
    const originalText = submitBtn.html();
    submitBtn.html('<i class="fas fa-spinner fa-spin me-2"></i>Guardando...');
    submitBtn.prop('disabled', true);

    $.ajax({
        url: "Grupo_ajax.php",
        type: "POST",
        data: $(this).serialize(),
        success: function(res){
            // Restaurar botón
            submitBtn.html(originalText);
            submitBtn.prop('disabled', false);

            if(res == "ok"){
                Swal.fire({
                    title: "✅ Éxito",
                    text: "Grupo registrado correctamente",
                    icon: "success",
                    confirmButtonColor: "#004aad"
                });
                $("#formGrupos")[0].reset();
                cargarGrupos();
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

// Cargar grupos al iniciar
$(document).ready(function(){
    cargarGrupos();
});
</script>

<?php include "includes/footer.php"; ?>