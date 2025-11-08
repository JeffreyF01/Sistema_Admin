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
                        <i class="fas fa-warehouse me-2"></i>Mantenimiento de Almacenes
                    </h4>
                    <p class="page-subtitle">Registrar y administrar almacenes del sistema</p>
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
            <div class="col-12">  <!-- Cambié a col-12 para que ocupe todo el ancho disponible -->
                <div class="card card-custom fade-in">
                    <div class="card-header card-header-custom">
                        <h4 class="mb-0">  <!-- Cambié h5 por h4 para más grande -->
                            <i class="fas fa-plus-circle me-2"></i>Registro de Almacenes
                        </h4>
                    </div>
                    <div class="card-body">
                        <form id="formAlmacen" class="form-mantenimiento">
                            <div class="row">
                                <div class="col-lg-6 col-md-6 mb-4">  <!-- Ajusté columnas -->
                                    <label class="form-label text-required fs-5">Código del Almacén</label>  <!-- fs-5 para texto más grande -->
                                    <input type="text" name="codigo" class="form-control form-control-custom fs-6" required 
                                           placeholder="(Ej: ALM-001)">
                                </div>
                                <div class="col-lg-6 col-md-6 mb-4">
                                    <label class="form-label text-required fs-5">Nombre del Almacén</label>
                                    <input type="text" name="nombre" class="form-control form-control-custom fs-6" required 
                                           placeholder="(Ej: Almacén Principal)">
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-lg-6 col-md-6 mb-4">
                                    <label class="form-label text-required fs-5">Estado del Almacén</label>
                                    <select name="activo" class="form-control form-control-custom fs-6" required>
                                        <option value="">Seleccione el estado del almacén</option>
                                        <option value="1">🟢 Activo</option>
                                        <option value="0">🔴 Inactivo</option>
                                    </select>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-5 pt-4 border-top">
                                <a href="dashboard.php" class="btn btn-secondary-custom btn-custom fs-6">
                                    <i class="fas fa-arrow-left me-2"></i>
                                </a>
                                <button type="submit" class="btn btn-primary-custom btn-custom fs-6">
                                    <i class="fas fa-save me-2"></i>Guardar Almacén
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$("#formAlmacen").on("submit", function(e){
    e.preventDefault();
    $.ajax({
        url: "Almacen_ajax.php",
        type: "POST",
        data: $(this).serialize(),
        success: function(res){
            if(res.trim() === "ok"){
                Swal.fire({
                    title: "✅ Éxito",
                    text: "Almacén registrado correctamente",
                    icon: "success",
                    confirmButtonColor: "#004aad"
                });
                $("#formAlmacen")[0].reset();
            } else {
                Swal.fire({
                    title: "❌ Error",
                    text: res,
                    icon: "error", 
                    confirmButtonColor: "#dc3545"
                });
            }
        }
    });
});
</script>

<?php include "includes/footer.php"; ?>