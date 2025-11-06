<?php
session_start();
if(!isset($_SESSION['usuario'])) {
  header("Location: index.html");
  exit();
}

require "conexion.php";
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Almacenes</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-dark text-light">

<div class="container mt-5">
<div class="card shadow-lg p-4 bg-secondary">

<h3 class="text-center mb-4">🏬 Registro de Almacenes</h3>

<form id="formAlmacen">

<div class="row">
  <div class="col-md-6 mb-3">
    <label>Código</label>
    <input type="text" name="codigo" class="form-control" required>
  </div>

  <div class="col-md-6 mb-3">
    <label>Nombre del Almacén</label>
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

<button type="submit" class="btn btn-primary w-100">Guardar Almacén</button>

</form>
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
        Swal.fire("✅ Éxito", "Almacén registrado correctamente", "success");
        $("#formAlmacen")[0].reset();
      } else {
        Swal.fire("❌ Error", res, "error");
      }

    }
  });

});
</script>

</body>
</html>
