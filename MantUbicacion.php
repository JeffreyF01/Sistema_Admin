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

    <div class="content-area">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="card card-custom fade-in">
                        <div class="card-header card-header-custom">
                            <h5 class="mb-0">
                                <i class="fas fa-plus-circle me-2"></i>Registro de Ubicaciones
                            </h5>
                        </div>
                        <div class="card-body">
                            <form id="formUbicacion" class="form-mantenimiento">
                                
                                <div class="row">
                                    <div class="col-md-4 mb-4">
                                        <label class="form-label text-required">Código de Ubicación</label>
                                        <input type="text" name="codigo" class="form-control form-control-custom" required 
                                               placeholder="Ej: UBI-001">
                                        
                                    </div>

                                    <div class="col-md-8 mb-4">
                                        <label class="form-label text-required">Nombre de la Ubicación</label>
                                        <input type="text" name="nombre" class="form-control form-control-custom" required 
                                               placeholder="Ej: Estantería A - Nivel 1">
                                       
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-4">
                                        <label class="form-label text-required">Almacén</label>
                                        <select name="id_almacen" class="form-control form-control-custom" required>
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
                                        <select name="activo" class="form-control form-control-custom" required>
                                            <option value="1">🟢 Activo</option>
                                            <option value="0">🔴 Inactivo</option>
                                        </select>
                                   
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                                    <a href="dashboard.php" class="btn btn-secondary-custom btn-custom">
                                        <i class="fas fa-arrow-left me-2"></i>Volver al Dashboard
                                    </a>
                                    <button type="submit" class="btn btn-primary-custom btn-custom">
                                        <i class="fas fa-save me-2"></i>Guardar Ubicación
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$("#formUbicacion").on("submit", function(e){
    e.preventDefault();

    // Mostrar loading
    const submitBtn = $(this).find('button[type="submit"]');
    const originalText = submitBtn.html();
    submitBtn.html('<i class="fas fa-spinner fa-spin me-2"></i>Guardando...');
    submitBtn.prop('disabled', true);

    $.ajax({
        url: "ubicacion_ajax.php",
        type: "POST",
        data: $(this).serialize(),
        success: function(res){
            // Restaurar botón
            submitBtn.html(originalText);
            submitBtn.prop('disabled', false);

            if(res.trim() == "ok"){
                Swal.fire({
                    title: "✅ Éxito",
                    text: "Ubicación registrada correctamente",
                    icon: "success",
                    confirmButtonColor: "#004aad"
                });
                $("#formUbicacion")[0].reset();
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
</script>

<?php include "includes/footer.php"; ?>
