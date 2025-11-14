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
                    <h4 class="page-title"><i class="fas fa-box me-2"></i>Mantenimiento de Productos</h4>
                    <p class="page-subtitle">Registrar y administrar productos del sistema</p>
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
                        <i class="fas fa-plus-circle me-2"></i> Añadir Producto
                    </button>
                </div>
            </div>

            <!-- Modal -->
            <div class="modal fade" id="modalProducto" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="fas fa-box me-2"></i>Registro de Producto</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            <form id="formProducto">
                                <input type="hidden" name="id_productos" id="id_productos">

                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label">SKU</label>
                                        <input type="text" name="sku" id="sku" class="form-control" required>
                                    </div>
                                    <div class="col-md-8">
                                        <label class="form-label">Nombre</label>
                                        <input type="text" name="nombre" id="nombre" class="form-control" required>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Departamento</label>
                                        <select name="departamento_id" id="departamento_id" class="form-select" required>
                                            <option value="">Seleccione...</option>
                                            <?php
                                            $res = $conexion->query("SELECT id_departamentos, nombre FROM departamento WHERE activo=1");
                                            while($dep = $res->fetch_assoc()){ 
                                                echo "<option value='{$dep['id_departamentos']}'>{$dep['nombre']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Grupo</label>
                                        <select name="grupo_id" id="grupo_id" class="form-select" required>
                                            <option value="">Seleccione...</option>
                                            <?php
                                            $res = $conexion->query("SELECT id_grupos, nombre FROM grupo WHERE activo=1");
                                            while($g = $res->fetch_assoc()){ 
                                                echo "<option value='{$g['id_grupos']}'>{$g['nombre']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Ubicación</label>
                                        <select name="ubicacion_id" id="ubicacion_id" class="form-select" required>
                                            <option value="">Seleccione...</option>
                                            <?php
                                            $res = $conexion->query("SELECT id_ubicaciones, nombre FROM ubicacion WHERE activo=1");
                                            while($u = $res->fetch_assoc()){ 
                                                echo "<option value='{$u['id_ubicaciones']}'>{$u['nombre']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-3">
                                        <label class="form-label">Unidad</label>
                                        <input type="text" name="unidad" id="unidad" class="form-control" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Stock</label>
                                        <input type="number" step="1" min="0" name="stock" id="stock" class="form-control" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Stock Mín.</label>
                                        <input type="number" step="1" min="0" name="stock_min" id="stock_min" class="form-control" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Estado</label>
                                        <select name="activo" id="activo" class="form-select">
                                            <option value="1">🟢 Activo</option>
                                            <option value="0">🔴 Inactivo</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Precio Venta</label>
                                        <input type="number" step="0.01" name="precio_venta" id="precio_venta" class="form-control" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Costo</label>
                                        <input type="number" step="0.01" name="costo" id="costo" class="form-control" required>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary-custom w-100">
                                    <i class="fas fa-save me-2"></i>Guardar Producto
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla -->
            <div class="card card-custom fade-in">
                <div class="card-header card-header-custom">
                    <h5><i class="fas fa-list me-2"></i>Lista de Productos</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-mantenimiento table-sm align-middle text-center">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>SKU</th>
                                    <th>Nombre</th>
                                    <th>Departamento</th>
                                    <th>Grupo</th>
                                    <th>Stock</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tablaProducto"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// ================= FUNCIONES ================= //
function cargarProductos(){
    $.get("Producto_listar.php", function(data){
        $("#tablaProducto").html(data);
    });
}

function eliminar(id){
    Swal.fire({
        title: "¿Eliminar producto?",
        text: "Esta acción no se puede deshacer.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#dc3545",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "Sí, eliminar"
    }).then((result) => {
        if (result.isConfirmed) {
            $.post("Producto_ajax.php", { accion: "eliminar", id_productos: id }, function(res){
                if(res.trim() === "ok"){
                    Swal.fire("Eliminado", "El producto fue eliminado correctamente", "success");
                    cargarProductos();
                } else {
                    Swal.fire("Error", res, "error");
                }
            });
        }
    });
}

$(document).on("click", ".btn-editar", function(){
    $("#id_productos").val($(this).data("id"));
    $("#sku").val($(this).data("sku"));
    $("#nombre").val($(this).data("nombre"));
    $("#departamento_id").val($(this).data("departamento"));
    $("#grupo_id").val($(this).data("grupo"));
    $("#ubicacion_id").val($(this).data("ubicacion"));
    $("#unidad").val($(this).data("unidad"));
    $("#precio_venta").val($(this).data("precio"));
    $("#costo").val($(this).data("costo"));
    $("#stock").val($(this).data("stock"));
    $("#stock_min").val($(this).data("stockmin"));
    $("#activo").val($(this).data("activo"));

    let modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalProducto'));
    modal.show();
});

$("#btnAbrirModal").click(function(){
    $("#formProducto")[0].reset();
    $("#id_productos").val('');
    let modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalProducto'));
    modal.show();
});

$("#formProducto").on("submit", function(e){
    e.preventDefault();

    let accion = $("#id_productos").val() ? "editar" : "agregar";
    let formData = $(this).serialize() + "&accion=" + accion;

    $.post("Producto_ajax.php", formData, function(res){
        if(res.trim() === "ok"){
            Swal.fire("✅ Éxito", "Producto guardado correctamente", "success");
            let modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalProducto'));
            modal.hide();
            $("#formProducto")[0].reset();
            cargarProductos();
        } else {
            Swal.fire("❌ Error", res, "error");
        }
    });
});

$(document).ready(function(){
    cargarProductos();
});
</script>

<?php include "includes/footer.php"; ?>
