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
                    <h4 class="page-title"><i class="fas fa-user-gear me-2"></i>Mantenimiento de Usuarios</h4>
                    <p class="page-subtitle">Registrar y administrar usuarios del sistema</p>
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
                        <i class="fas fa-plus-circle me-2"></i> Añadir Usuario
                    </button>
                </div>
            </div>

            <!-- Modal -->
            <div class="modal fade" id="modalUsuario" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="fas fa-user-gear me-2"></i>Registro de Usuario</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            <form id="formUsuario">
                                <input type="hidden" name="id_usuarios" id="id_usuarios">

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Nombre</label>
                                        <input type="text" name="nombre" id="nombre" class="form-control" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Usuario</label>
                                        <input type="text" name="usuario" id="usuario" class="form-control" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Clave</label>
                                        <input type="password" name="clave" id="clave" class="form-control" required>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Rol</label>
                                        <select name="id_rol" id="id_rol" class="form-control" required>
                                            <option value="">Seleccione un rol...</option>
                                            <?php
                                            $roles = $conexion->query("SELECT id_roles, nombre FROM role WHERE activo = 1 ORDER BY nombre");
                                            while($r = $roles->fetch_assoc()){
                                                echo "<option value='".$r['id_roles']."'>".$r['nombre']."</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Estado</label>
                                        <select name="activo" id="activo" class="form-control">
                                            <option value="1">🟢 Activo</option>
                                            <option value="0">🔴 Inactivo</option>
                                        </select>
                                    </div>
                                </div>

                                <h6 class="mt-4 mb-3 fw-bold text-primary">
                                    <i class="fas fa-key me-2"></i>Permisos del Usuario
                                </h6>

                                <div class="row mb-3">
                                    <div class="col-md-3">
                                        <label class="form-label">Inventario</label><br>
                                        <div class="form-check"><input class="form-check-input" type="checkbox" name="inv_productos" id="inv_productos" value="1"> Productos</div>
                                        <div class="form-check"><input class="form-check-input" type="checkbox" name="inv_almacenes" id="inv_almacenes" value="1"> Almacenes</div>
                                        <div class="form-check"><input class="form-check-input" type="checkbox" name="inv_ubicaciones" id="inv_ubicaciones" value="1"> Ubicaciones</div>
                                        <div class="form-check"><input class="form-check-input" type="checkbox" name="inv_departamentos" id="inv_departamentos" value="1"> Departamentos</div>
                                        <div class="form-check"><input class="form-check-input" type="checkbox" name="inv_grupos" id="inv_grupos" value="1"> Grupos</div>
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">Procesos</label><br>
                                        <div class="form-check"><input class="form-check-input" type="checkbox" name="inv_cotizaciones" id="inv_cotizaciones" value="1"> Cotizaciones</div>
                                        <div class="form-check"><input class="form-check-input" type="checkbox" name="inv_compras" id="inv_compras" value="1"> Compras</div>
                                        <div class="form-check"><input class="form-check-input" type="checkbox" name="inv_movimientos" id="inv_movimientos" value="1"> Movimientos</div>
                                        <div class="form-check"><input class="form-check-input" type="checkbox" name="inv_devoluciones" id="inv_devoluciones" value="1"> Devoluciones</div>
                                        <div class="form-check"><input class="form-check-input" type="checkbox" name="inv_facturacion" id="inv_facturacion" value="1"> Facturación</div>
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">Consultas y Reportes</label><br>
                                        <div class="form-check"><input class="form-check-input" type="checkbox" name="inv_consultas" id="inv_consultas" value="1"> Consultas</div>
                                        <div class="form-check"><input class="form-check-input" type="checkbox" name="inv_reportes" id="inv_reportes" value="1"> Reportes</div>
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">Configuraciones</label><br>
                                        <div class="form-check"><input class="form-check-input" type="checkbox" name="conf_usuario" id="conf_usuario" value="1"> Usuario</div>
                                        <div class="form-check"><input class="form-check-input" type="checkbox" name="conf_roles" id="conf_roles" value="1"> Roles</div>
                                        <div class="form-check"><input class="form-check-input" type="checkbox" name="conf_empresa" id="conf_empresa" value="1"> Empresa</div>
                                        <div class="form-check"><input class="form-check-input" type="checkbox" name="conf_moneda" id="conf_moneda" value="1"> Moneda</div>
                                        <div class="form-check"><input class="form-check-input" type="checkbox" name="conf_condicion" id="conf_condicion" value="1"> Condición</div>
                                    </div>
                                </div>

                                <h6 class="mt-4 mb-3 fw-bold text-primary">
                                    <i class="fas fa-handshake me-2"></i>Externos
                                </h6>

                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <div class="form-check"><input class="form-check-input" type="checkbox" name="exc_clientes" id="exc_clientes" value="1"> Clientes</div>
                                        <div class="form-check"><input class="form-check-input" type="checkbox" name="exc_cobros" id="exc_cobros" value="1"> Cobros</div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check"><input class="form-check-input" type="checkbox" name="exp_proveedores" id="exp_proveedores" value="1"> Proveedores</div>
                                        <div class="form-check"><input class="form-check-input" type="checkbox" name="exp_pagos" id="exp_pagos" value="1"> Pagos</div>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary-custom w-100">
                                    <i class="fas fa-save me-2"></i>Guardar Usuario
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla -->
            <div class="card card-custom fade-in mt-4">
                <div class="card-header card-header-custom">
                    <h5><i class="fas fa-list me-2"></i>Lista de Usuarios</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-mantenimiento align-middle text-center">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Usuario</th>
                                    <th>Rol</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tablaUsuario"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function cargarUsuarios(){
    $.get("Usuario_listar.php", function(data){
        $("#tablaUsuario").html(data);
    });
}

// 🔹 Cargar datos para editar con permisos
function editar(id){
    $.get("Usuario_ajax.php", { obtener: id }, function(res){
        const data = JSON.parse(res);
        if(data.error){ 
            Swal.fire("❌ Error", data.error, "error"); 
            return;
        }

        $("#id_usuarios").val(data.id_usuarios);
        $("#nombre").val(data.nombre);
        $("#usuario").val(data.usuario);
        $("#clave").val(data.clave);
        $("#id_rol").val(data.id_rol);
        $("#activo").val(data.activo);

        // Marcar permisos según valores 0/1
        $("input[type=checkbox]").each(function(){
            const name = $(this).attr("name");
            $(this).prop("checked", data[name] == 1);
        });

        const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalUsuario'));
        modal.show();
    });
}

function eliminar(id){
    Swal.fire({
        title: "¿Eliminar usuario?",
        text: "Esta acción no se puede deshacer.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#dc3545",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "Sí, eliminar"
    }).then((result) => {
        if (result.isConfirmed) {
            $.post("Usuario_ajax.php", { eliminar: true, id_usuarios: id }, function(res){
                if(res.trim() === "ok"){
                    Swal.fire("Eliminado", "El usuario fue eliminado correctamente", "success");
                    cargarUsuarios();
                } else {
                    Swal.fire("Error", res, "error");
                }
            });
        }
    });
}

$("#btnAbrirModal").click(function(){
    $("#formUsuario")[0].reset();
    $("#id_usuarios").val('');
    const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalUsuario'));
    modal.show();
});

// 🟢 Guardar usuario
$("#formUsuario").on("submit", function(e){
    e.preventDefault();
    $.post("Usuario_ajax.php", $(this).serialize(), function(res){
        if(res.trim() === "ok"){
            Swal.fire("✅ Éxito", "Usuario guardado correctamente", "success");
            const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalUsuario'));
            modal.hide();
            $("#formUsuario")[0].reset();
            cargarUsuarios();
        } else {
            Swal.fire("❌ Error", res, "error");
        }
    });
});

// 🟡 Detectar cambio en el rol y aplicar permisos automáticos
$("#id_rol").change(function(){
    const rolSeleccionado = $("#id_rol option:selected").text().trim().toLowerCase();

    // 🔸 Limpia todo antes de aplicar lógica
    $("input[type=checkbox]").prop("checked", false);

    if (rolSeleccionado.includes("admin")) {
        // ✅ Si es administrador, marca todos los permisos
        $("input[type=checkbox]").prop("checked", true);
    } 
    else if (rolSeleccionado.includes("vendedor")) {
        // 🟠 Ejemplo: rol vendedor (solo ventas)
        $("#inv_facturacion, #inv_consultas, #inv_reportes, #exc_clientes, #exc_cobros").prop("checked", true);
    } 
    else if (rolSeleccionado.includes("invitado")) {
        // 🔵 Invitado (solo consultas)
        $("#inv_consultas, #inv_reportes").prop("checked", true);
    }
    // Otros roles se pueden agregar igual
});

$(document).ready(function(){
    cargarUsuarios();
});
</script>


<?php include "includes/footer.php"; ?>
