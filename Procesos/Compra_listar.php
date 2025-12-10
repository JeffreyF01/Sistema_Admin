<?php
session_start();
if(!isset($_SESSION['usuario'])) {
    header("Location: ../index.html");
    exit();
}
require_once '../conexion.php';
include '../includes/header.php';
include '../includes/sidebar.php';
?>
<div class="main-content">
  <div class="main-header">
    <div class="container-fluid">
      <div class="row align-items-center">
        <div class="col">
          <h4 class="page-title"><i class="fa-solid fa-list me-2"></i>Listado de Compras</h4>
          <p class="page-subtitle">Compras registradas</p>
        </div>
      </div>
    </div>
  </div>

  <div class="content-area">
    <div class="container-fluid">
      <div class="mb-3">
        <a href="MantCompra.php" class="btn btn-primary"><i class="fa-solid fa-plus me-2"></i>Nueva Compra</a>
      </div>

      <div class="card card-custom">
        <div class="card-header card-header-custom">
          <h5 class="mb-0"><i class="fa-solid fa-boxes-stacked me-2"></i>Compras</h5>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-mantenimiento" id="compraTableList">
              <thead>
                <tr>
                  <th>N° Compra</th>
                  <th>Proveedor</th>
                  <th>Fecha</th>
                  <th>Total</th>
                  <th>Estado</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody>
              <?php
                $q = "SELECT c.*, p.nombre as proveedor_nombre FROM compra c LEFT JOIN proveedor p ON c.proveedor_id = p.id_proveedores ORDER BY c.fecha ASC";
                $res = $conexion->query($q);
                if($res && $res->num_rows>0){
                    while($r = $res->fetch_assoc()){
                        $estado = $r['activo']==1 ? "<span class='badge bg-success'>Activo</span>" : "<span class='badge bg-secondary'>Anulado</span>";
                        echo "<tr>";
                        echo "<td>".htmlspecialchars($r['numero_documento'])."</td>";
                        echo "<td>".htmlspecialchars($r['proveedor_nombre'])."</td>";
                        echo "<td>".htmlspecialchars($r['fecha'])."</td>";
                        echo "<td>$".number_format($r['total'],2)."</td>";
                        echo "<td>$estado</td>";
                        echo "<td>
                                <a class='btn btn-sm btn-primary' href='Compra_imprimir.php?id={$r['id_compras']}' target='_blank'><i class='fa-solid fa-print'></i></a>
                              </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='6' class='text-center'>No hay compras registradas</td></tr>";
                }
              ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>
<?php include '../includes/footer.php'; ?>
