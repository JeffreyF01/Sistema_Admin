<?php
session_start();
if(!isset($_SESSION['usuario'])) {
  header("Location: index.html");
  exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Departamentos</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-dark text-light">

<div class="container mt-5">
<div class="card shadow-lg p-4 bg-secondary">

<h3 class="text-center mb-4">🏢 Registro de Departamentos</h3>

<form id="formDepartamentos">

<input type="hidden" name="id_departamentos" id="id_departamentos">

<div class="row mb-3">
  <div class="col-md-8">
    <label>Nombre</label>
    <input type="text" name="nombre" id="nombre" class="form-control" required>
  </div>
  
  <div class="col-md-4">
    <label>Activo</label>
    <select name="activo" id="activo" class="form-control" required>
      <option value="1">Sí</option>
      <option value="0">No</option>
    </select>
  </div>
</div>

<button type="submit" class="btn btn-primary w-100">Guardar Departamento</button>

</form>

<hr class="text-white">

<h4 class="text-center mb-3">📋 Lista de Departamentos</h4>

<table class="table table-dark table-bordered table-striped">
<thead>
<tr>
  <th>ID</th>
  <th>Nombre</th>
  <th>Estado</th>
  <th>Acciones</th>
</tr>
</thead>
<tbody id="tablaDepartamentos"></tbody>
</table>

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
}

$("#formDepartamentos").on("submit", function(e){
  e.preventDefault();

  $.ajax({
    url: "Departamento_ajax.php",
    type: "POST",
    data: $(this).serialize(),
    success: function(res){
      if(res == "ok"){
        Swal.fire("✅ Éxito", "Departamento guardado correctamente", "success");
        $("#formDepartamentos")[0].reset();
        $("#id_departamentos").val("");
        cargarDepartamentos();
      } else {
        Swal.fire("❌ Error", res, "error");
      }
    }
  });
});

cargarDepartamentos();
</script>

</body>
</html>
