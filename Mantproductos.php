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
<title>Productos</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-dark text-light">

<div class="container mt-5">
<div class="card shadow-lg p-4 bg-secondary">

<h3 class="text-center mb-4">📦 Registro de Productos</h3>

<form id="formProductos">

<div class="row">
  <div class="col-md-4 mb-3">
    <label>SKU</label>
    <input type="text" name="sku" class="form-control" required>
  </div>

  <div class="col-md-8 mb-3">
    <label>Nombre del Producto</label>
    <input type="text" name="nombre" class="form-control" required>
  </div>
</div>

<div class="row">
  <div class="col-md-4 mb-3">
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

  <div class="col-md-4 mb-3">
    <label>Grupo</label>
    <select name="grupo_id" class="form-control" required>
      <option value="">Seleccione...</option>
      <?php
        $sql = $conexion->query("SELECT id_grupos, nombre FROM grupo");
        while($g = $sql->fetch_assoc()){
          echo "<option value='".$g['id_grupos']."'>".$g['nombre']."</option>";
        }
      ?>
    </select>
  </div>

  <div class="col-md-4 mb-3">
    <label>Unidad</label>
    <input type="text" name="unidad" class="form-control" required>
  </div>
</div>

<div class="row">
  <div class="col-md-4 mb-3">
    <label>Precio de Venta</label>
    <input type="number" step="0.01" name="precio_venta" class="form-control" required>
  </div>

  <div class="col-md-4 mb-3">
    <label>Costo</label>
    <input type="number" step="0.01" name="costo" class="form-control" required>
  </div>

  <div class="col-md-4 mb-3">
    <label>Stock</label>
    <input type="number" name="stock" class="form-control" required>
  </div>

  <div class="col-md-4 mb-3">
    <label>Stock Mínimo</label>
    <input type="number" name="stock_min" class="form-control" required>
  </div>

  <div class="col-md-4 mb-3">
    <label>Ubicación</label>
    <select name="ubicacion_id" class="form-control" required>
      <option value="">Seleccione...</option>
      <?php
        $sql = $conexion->query("SELECT id_ubicaciones, nombre FROM ubicacion");
        while($u = $sql->fetch_assoc()){
          echo "<option value='".$u['id_ubicaciones']."'>".$u['nombre']."</option>";
        }
      ?>
    </select>
  </div>

  <div class="col-md-4 mb-3">
    <label>Activo</label>
    <select name="activo" class="form-control" required>
      <option value="1">Sí</option>
      <option value="0">No</option>
    </select>
  </div>
</div>

<button type="submit" class="btn btn-primary w-100">Guardar Producto</button>

</form>

</div>
</div>

<script>
$("#formProductos").on("submit", function(e){
  e.preventDefault();

  $.ajax({
    url: "Ajax/producto_ajax.php",
    type: "POST",
    data: $(this).serialize(),
    success: function(res){

      if(res == "ok"){
        Swal.fire("✅ Éxito", "Producto registrado correctamente", "success");
        $("#formProductos")[0].reset();
      } else {
        Swal.fire("❌ Error", res, "error");
      }

    }
  });

});
</script>

</body>
</html>
