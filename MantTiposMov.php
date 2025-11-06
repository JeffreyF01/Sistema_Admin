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
<title>Tipos de Movimiento</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-dark text-light">

<div class="container mt-5">
<div class="card shadow-lg p-4 bg-secondary">

<h3 class="text-center mb-4">⚙️ Registro de Tipos de Movimiento</h3>

<form id="formMovimientos">

<div class="row mb-3">
  <div class="col-md-6">
    <label>Nombre</label>
    <input type="text" name="nombre" class="form-control" required>
  </div>
  <div class="col-md-6">
    <label>Clase</label>
    <select name="clase" class="form-control" required>
        <option value="">Seleccione...</option>
        <option value="Entrada">Entrada</option>
        <option value="Salida">Salida</option>
    </select>
  </div>
</div>

<div class="mb-3">
  <label>Descripción</label>
  <textarea name="descripcion" class="form-control" rows="2"></textarea>
</div>

<div class="col-md-4 mb-3">
  <label>Activo</label>
  <select name="activo" class="form-control" required>
    <option value="1">Sí</option>
    <option value="0">No</option>
  </select>
</div>

<button type="submit" class="btn btn-primary w-100">Guardar Tipo de Movimiento</button>

</form>

<hr class="text-white">

<h4 class="text-center mb-3">📋 Lista de Tipos de Movimiento</h4>

<table class="table table-dark table-bordered table-striped">
<thead>
<tr>
  <th>ID</th>
  <th>Nombre</th>
  <th>Clase</th>
  <th>Descripción</th>
  <th>Estado</th>
</tr>
</thead>
<tbody id="tablaMovimientos"></tbody>
</table>

</div>
</div>

<script>
function cargarMovimientos(){
  $.ajax({
    url: "TiposMov_listar.php",
    type: "GET",
    success: function(data){
      $("#tablaMovimientos").html(data);
    }
  });
}

$("#formMovimientos").on("submit", function(e){
  e.preventDefault();

  $.ajax({
    url: "TiposMov_ajax.php",
    type: "POST",
    data: $(this).serialize(),
    success: function(res){
      if(res == "ok"){
        Swal.fire("✅ Éxito", "Tipo de movimiento registrado correctamente", "success");
        $("#formMovimientos")[0].reset();
        cargarMovimientos();
      } else {
        Swal.fire("❌ Error", res, "error");
      }
    }
  });
});

cargarMovimientos();
</script>

</body>
</html>
