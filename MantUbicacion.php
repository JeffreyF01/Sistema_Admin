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
<title>Ubicaciones</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-dark text-light">

<div class="container mt-5">
<div class="card shadow-lg p-4 bg-secondary">

<h3 class="text-center mb-4">📍 Registro de Ubicaciones</h3>

<form id="formUbicacion">

<div class="row">

  <div class="col-md-4 mb-3">
    <label>Código</label>
    <input type="text" name="codigo" class="form-control" required>
  </div>

  <div class="col-md-8 mb-3">
    <label>Nombre</label>
    <input type="text" name="nombre" class="form-control" required>
  </div>

</div>

<div class="row">
  <div class="col-md-6 mb-3">
    <label>Almacén</label>
    <select name="id_almacen" class="form-control" required>
      <option value="">Seleccione...</option>

      <?php
        require "conexion.php";
        $sql = $conexion->query("SELECT id_almacen, nombre FROM almacen WHERE activo = 1");
        while($a = $sql->fetch_assoc()){
          echo "<option value='".$a['id_almacen']."'>".$a['nombre']."</option>";
        }
      ?>

    </select>
  </div>

  <div class="col-md-6 mb-3">
    <label>Activo</label>
    <select name="activo" class="form-control" required>
      <option value="1">Sí</option>
      <option value="0">No</option>
    </select>
  </div>

</div>

<button type="submit" class="btn btn-primary w-100">Guardar Ubicación</button>

</form>

</div>
</div>

<script>
$("#formUbicacion").on("submit", function(e){
  e.preventDefault();

  $.ajax({
    url: "ubicacion_ajax.php",
    type: "POST",
    data: $(this).serialize(),
    success: function(res){
      if(res.trim() == "ok"){
        Swal.fire("✅ Éxito", "Ubicación registrada correctamente", "success");
        $("#formUbicacion")[0].reset();
      } else {
        Swal.fire("❌ Error", res, "error");
      }
    }
  });

});
</script>

</body>
</html>
