<?php
session_start();
require_once '../conexion.php';

header('Content-Type: application/json');

// Verificar sesión
if(!isset($_SESSION['usuario'])) {
    echo json_encode(['success' => false, 'message' => 'Sesión no válida']);
    exit();
}

try {
    $query = "SELECT d.*, 
                     f.numero_documento as factura_numero, 
                     c.nombre as cliente_nombre, 
                     u.nombre as usuario_nombre
              FROM devolucion d
              INNER JOIN factura f ON d.factura_id = f.id_facturas
              INNER JOIN cliente c ON f.cliente_id = c.id_clientes
              INNER JOIN usuario u ON d.usuario_id = u.id_usuarios
              ORDER BY d.fecha DESC, d.id_devoluciones DESC";
    
    $result = $conexion->query($query);
    $devoluciones = [];

    while($row = $result->fetch_assoc()) {
        $devoluciones[] = $row;
    }

    echo json_encode(['success' => true, 'data' => $devoluciones]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
