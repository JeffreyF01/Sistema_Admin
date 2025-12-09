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
                    <h4 class="page-title"><i class="fas fa-building me-2"></i>Mantenimiento de Empresa</h4>
                    <p class="page-subtitle">Registrar y administrar la información de la empresa</p>
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
                        <i class="fas fa-plus-circle me-2"></i> Añadir Empresa
                    </button>
                </div>
            </div>

            <!-- Modal -->
            <div class="modal fade" id="modalEmpresa" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="fas fa-building me-2"></i>Registro de Empresa</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            <form id="formEmpresa" enctype="multipart/form-data">
                                <input type="hidden" name="id_empresa" id="id_empresa">

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Nombre <span class="text-danger">*</span></label>
                                        <input type="text" name="nombre" id="nombre" class="form-control" 
                                               maxlength="160" 
                                               placeholder="Nombre de la empresa" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">RNC <span class="text-danger">*</span></label>
                                        <input type="text" name="rnc" id="rnc" class="form-control" 
                                               pattern="[0-9\-]+" 
                                               title="Solo números y guiones" 
                                               maxlength="30" 
                                               placeholder="000-0000000-0" required>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Dirección <span class="text-danger">*</span></label>
                                        <input type="text" name="direccion" id="direccion" class="form-control" 
                                               maxlength="255" 
                                               placeholder="Calle, número, ciudad" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Teléfono <span class="text-danger">*</span></label>
                                        <input type="text" name="telefono" id="telefono" class="form-control" 
                                               pattern="[0-9\-\+\(\)\s]+" 
                                               title="Solo números, espacios, guiones, paréntesis" 
                                               maxlength="40" 
                                               placeholder="(809) 555-1234" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Email <span class="text-danger">*</span></label>
                                        <input type="email" name="email" id="email" class="form-control" 
                                               maxlength="120" 
                                               placeholder="empresa@correo.com" required>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Moneda Base</label>
                                        <select name="moneda_base_id" id="moneda_base_id" class="form-control" required>
                                            <option value="">Seleccione...</option>
                                            <?php
                                            $monedas = $conexion->query("SELECT id_monedas, nombre, simbolo FROM moneda WHERE activo = 1");
                                            while($m = $monedas->fetch_assoc()){
                                                echo "<option value='".$m['id_monedas']."'>".$m['nombre']." (".$m['simbolo'].")</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Logo (opcional)</label>
                                        <input type="file" name="logo_url" id="logo_url" class="form-control" accept="image/*">
                                        <img id="previewLogo" src="" alt="Vista previa del logo" class="mt-2" style="max-height:80px; display:none;">
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary-custom w-100">
                                    <i class="fas fa-save me-2"></i>Guardar Empresa
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla -->
            <div class="card card-custom fade-in mt-4">
                <div class="card-header card-header-custom">
                    <h5><i class="fas fa-list me-2"></i>Lista de Empresas</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-mantenimiento align-middle text-center">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>RNC</th>
                                    <th>Teléfono</th>
                                    <th>Email</th>
                                    <th>Moneda Base</th>
                                    <th>Logo</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tablaEmpresa"></tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
function cargarEmpresas(){
    $.get("Empresa_listar.php", function(data){
        $("#tablaEmpresa").html(data);
    });
}

function editar(id, nombre, rnc, direccion, telefono, email, moneda_base_id, logo_url){
    $("#id_empresa").val(id);
    $("#nombre").val(nombre);
    $("#rnc").val(rnc);
    $("#direccion").val(direccion);
    $("#telefono").val(telefono);
    $("#email").val(email);
    $("#moneda_base_id").val(moneda_base_id);

    if(logo_url){
        $("#previewLogo").attr("src", logo_url).show();
    } else {
        $("#previewLogo").hide();
    }

    let modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalEmpresa'));
    modal.show();
}

function eliminar(id){
    Swal.fire({
        title: "¿Eliminar empresa?",
        text: "Esta acción no se puede deshacer.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#dc3545",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "Sí, eliminar"
    }).then((result) => {
        if (result.isConfirmed) {
            $.post("Empresa_ajax.php", { eliminar: true, id_empresa: id }, function(res){
                if(res.trim() === "ok"){
                    Swal.fire("Eliminado", "La empresa fue eliminada correctamente", "success");
                    cargarEmpresas();
                } else {
                    Swal.fire("Error", res, "error");
                }
            });
        }
    });
}

$("#btnAbrirModal").click(function(){
    $("#formEmpresa")[0].reset();
    $("#id_empresa").val('');
    $("#previewLogo").hide();
    let modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalEmpresa'));
    modal.show();
});

$("#logo_url").change(function(){
    const file = this.files[0];
    if(file){
        const reader = new FileReader();
        reader.onload = function(e){
            $("#previewLogo").attr("src", e.target.result).show();
        }
        reader.readAsDataURL(file);
    }
});

$("#formEmpresa").on("submit", function(e){
    e.preventDefault();
    let formData = new FormData(this);
    $.ajax({
        url: "Empresa_ajax.php",
        type: "POST",
        data: formData,
        processData: false,
        contentType: false,
        success: function(res){
            if(res.trim() === "ok"){
                Swal.fire("✅ Éxito", "Empresa guardada correctamente", "success");
                let modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalEmpresa'));
                modal.hide();
                $("#formEmpresa")[0].reset();
                $("#previewLogo").hide();
                cargarEmpresas();
            } else {
                Swal.fire("❌ Error", res, "error");
            }
        }
    });
});

$(document).ready(function(){
    cargarEmpresas();
});
</script>

<?php include "includes/footer.php"; ?>
