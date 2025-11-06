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
<title>Grupos</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-dark text-light">

<div class="container mt-5">
<div class="card shadow-lg p-4 bg-secondary">

<h3 class="text-center mb-4">📂 Registro de Grupos</h3>

<form id="formGrupos">

<div class="row">
  <div class="col-md-6 mb-3">
    <label>Departamento</label>
    <select name="departamento_id" class="form-control" required>
      <option value="">Seleccione...</option>
      <?php
        require "conexion.php";
        $sql = $conexion->query("SELECT id_departamentos, nombre FROM departamento");
        while($d = $sql->fetch_assoc()){
          echo "<option value='".$d['id_departamentos']."'>".$d['nombre']."</option>";
        }
      ?>
    </select>
  </div>

  <div class="col-md-6 mb-3">
    <label>Nombre del Grupo</label>
    <input type="text" name="nombre" class="form-control" required>
  </div>
</div>

<div class="row">
  <div class="col-md-4 mb-3">
    <label>Activo</label>
    <select name="activo" class="form-control" required>
      <option value="1">Sí</option>
      <option value="0">No</option>
    </select>
  </div>
</div>

<button type="submit" class="btn btn-primary w-100">Guardar Grupo</button>
</form>

<hr class="text-white">

<h4 class="text-center mb-3">📋 Lista de Grupos</h4>

<table class="table table-dark table-bordered table-striped">
<thead>
<tr>
  <th>ID</th>
  <th>Departamento</th>
  <th>Grupo</th>
  <th>Estado</th>
</tr>
</thead>
<tbody id="tablaGrupos"></tbody>
</table>

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

  $.ajax({
    url: "Grupo_ajax.php",
    type: "POST",
    data: $(this).serialize(),
    success: function(res){
      if(res == "ok"){
        Swal.fire("✅ Éxito", "Grupo registrado correctamente", "success");
        $("#formGrupos")[0].reset();
        cargarGrupos();
      } else {
        Swal.fire("❌ Error", res, "error");
      }
    }
  });
});

cargarGrupos();
</script>

</body>
</html>
