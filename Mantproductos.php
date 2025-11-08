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
                        <i class="fas fa-box me-2"></i>Mantenimiento de Productos
                    </h4>
                    <p class="page-subtitle">Gestionar productos del inventario del sistema</p>
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
                <div class="col-12">
                    <div class="card card-custom fade-in">
                        <div class="card-header card-header-custom">
                            <h5 class="mb-0">
                                <i class="fas fa-plus-circle me-2"></i>Registro de Productos
                            </h5>
                        </div>
                        <div class="card-body">
                            <form id="formProductos" class="form-mantenimiento">
                                
                                <div class="row">
                                    <div class="col-md-4 mb-4">
                                        <label class="form-label text-required">SKU del Producto</label>
                                        <input type="text" name="sku" class="form-control form-control-custom" required 
                                               placeholder="Ej: PROD-001, SKU-2024">
                                    </div>

                                    <div class="col-md-8 mb-4">
                                        <label class="form-label text-required">Nombre del Producto</label>
                                        <input type="text" name="nombre" class="form-control form-control-custom" required 
                                               placeholder="Ej: Laptop Dell Inspiron 15, Smartphone Samsung Galaxy">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4 mb-4">
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

                                    <div class="col-md-4 mb-4">
                                        <label class="form-label text-required">Grupo</label>
                                        <select name="grupo_id" class="form-control form-control-custom" required>
                                            <option value="">Seleccione un grupo...</option>
                                            <?php
                                            $sql = $conexion->query("SELECT id_grupos, nombre FROM grupo WHERE activo = 1 ORDER BY nombre");
                                            while($g = $sql->fetch_assoc()){
                                                echo "<option value='".$g['id_grupos']."'>".$g['nombre']."</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>

                                    <div class="col-md-4 mb-4">
                                        <label class="form-label text-required">Unidad de Medida</label>
                                        <input type="text" name="unidad" class="form-control form-control-custom" required 
                                               placeholder="Ej: Unidad, Pieza, Kg, Litro">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-3 mb-4">
                                        <label class="form-label text-required">Precio de Venta</label>
                                        <input type="number" step="0.01" name="precio_venta" class="form-control form-control-custom" required 
                                               placeholder="0.00" min="0">
                                    </div>

                                    <div class="col-md-3 mb-4">
                                        <label class="form-label text-required">Costo</label>
                                        <input type="number" step="0.01" name="costo" class="form-control form-control-custom" required 
                                               placeholder="0.00" min="0">
                                    </div>

                                    <div class="col-md-3 mb-4">
                                        <label class="form-label text-required">Stock Actual</label>
                                        <input type="number" name="stock" class="form-control form-control-custom" required 
                                               placeholder="0" min="0">
                                    </div>

                                    <div class="col-md-3 mb-4">
                                        <label class="form-label text-required">Stock Mínimo</label>
                                        <input type="number" name="stock_min" class="form-control form-control-custom" required 
                                               placeholder="0" min="0">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-4">
                                        <label class="form-label text-required">Ubicación</label>
                                        <select name="ubicacion_id" class="form-control form-control-custom" required>
                                            <option value="">Seleccione una ubicación...</option>
                                            <?php
                                            $sql = $conexion->query("SELECT id_ubicaciones, nombre FROM ubicacion WHERE activo = 1 ORDER BY nombre");
                                            while($u = $sql->fetch_assoc()){
                                                echo "<option value='".$u['id_ubicaciones']."'>".$u['nombre']."</option>";
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
                                        <i class="fas fa-save me-2"></i>Guardar Producto
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
$("#formProductos").on("submit", function(e){
    e.preventDefault();

    // Mostrar loading
    const submitBtn = $(this).find('button[type="submit"]');
    const originalText = submitBtn.html();
    submitBtn.html('<i class="fas fa-spinner fa-spin me-2"></i>Guardando...');
    submitBtn.prop('disabled', true);

    $.ajax({
        url: "Ajax/producto_ajax.php",
        type: "POST",
        data: $(this).serialize(),
        success: function(res){
            // Restaurar botón
            submitBtn.html(originalText);
            submitBtn.prop('disabled', false);

            if(res == "ok"){
                Swal.fire({
                    title: "✅ Éxito",
                    text: "Producto registrado correctamente",
                    icon: "success",
                    confirmButtonColor: "#004aad"
                });
                $("#formProductos")[0].reset();
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