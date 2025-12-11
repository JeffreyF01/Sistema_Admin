<?php
require_once "../conexion.php";

$data = json_decode(file_get_contents("php://input"), true);

if(!$data){ $data = $_POST; }

$accion = $data['accion'] ?? '';

switch($accion){

    case "listar":

    $sql = "SELECT 
                p.id_pagos,
                p.numero_documento AS numero_pago,
                pr.nombre AS proveedor,
                p.fecha AS fecha_pago,
                p.monto AS monto,
                u.nombre AS usuario
            FROM pago p
            LEFT JOIN proveedor pr ON pr.id_proveedores = p.proveedor_id
            LEFT JOIN usuario u ON u.id_usuarios = p.usuario_id
            WHERE p.activo = 1
            ORDER BY p.id_pagos DESC
            LIMIT 200";

    $res = $conexion->query($sql);

    $datos = [];
    while ($row = $res->fetch_assoc()) {

        $pago_id = $row['id_pagos'];

        // ===============================
        // BUSCAR EL ESTADO DEL PAGO
        // ===============================
        $sqlEstado = "SELECT cxp.saldo
                      FROM pago_detalle pd
                      INNER JOIN cxp ON cxp.compra_id = pd.compra_id
                      WHERE pd.pago_id = ?";

        $stmt = $conexion->prepare($sqlEstado);
        $stmt->bind_param("i", $pago_id);
        $stmt->execute();
        $resEstado = $stmt->get_result();

        $estado = "Sin Pagar";
        $tieneDetalle = false;

        while ($e = $resEstado->fetch_assoc()) {
            $tieneDetalle = true;
            if (floatval($e['saldo']) > 0) {
                $estado = "Parcial";
                break;
            }
        }

        if (!$tieneDetalle) {
            $estado = "Desconocido";
        }

        // agregar estado al resultado final
        $row['estado'] = $estado;

        $datos[] = $row;
    }

    echo json_encode([
        "success" => true,
        "data" => $datos
    ]);
    break;
    
// ============================
// LISTAR FACTURAS PENDIENTES
// ============================
case "facturasProveedor":

    $proveedor_id = intval($data['proveedor_id']);

        // Solo facturas a crédito: condicion_pago con días de plazo > 0
        $sql = "SELECT c.id_compras, c.numero_documento, c.fecha, 
                 c.total, cxp.saldo
             FROM cxp cxp
             INNER JOIN compra c ON c.id_compras = cxp.compra_id
             INNER JOIN condicion_pago cp ON cp.id_condiciones_pago = c.condicion_id
             WHERE cxp.proveedor_id = ?
            AND cp.dias_plazo > 0   -- crédito
            AND cxp.saldo > 0 
            AND cxp.activo = 1";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $proveedor_id);
    $stmt->execute();
    $res = $stmt->get_result();

    $arr = [];
    while($row = $res->fetch_assoc()) $arr[] = $row;

    echo json_encode(["success"=>true,"data"=>$arr]);
break;


// ====================================
// REGISTRAR PAGO COMPLETO
// ====================================
case "registrar_pago":

try{

    $proveedor_id = intval($data['proveedor_id']);
    $usuario_id   = intval($data['usuario_id']);
    $fecha_pago   = $data['fecha_pago'] ?? null;
    $metodo_pago  = $data['metodo_pago'] ?? 'Efectivo';
    $monto_total  = floatval($data['monto']);
    $detalles     = $data['detalle'];

    if(!$fecha_pago) throw new Exception("Fecha de pago no recibida");
    if(empty($detalles)) throw new Exception("No se recibieron detalles.");

    // INSERTAR PAGO
    $numero = "PAG-" . str_pad(rand(1,99999999),8,"0",STR_PAD_LEFT);

    $sql = "INSERT INTO pago (numero_documento, proveedor_id, usuario_id, fecha, monto, metodo_pago, activo)
            VALUES (?, ?, ?, ?, ?, ?, 1)";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("siisds", $numero, $proveedor_id, $usuario_id, $fecha_pago, $monto_total, $metodo_pago);
    $stmt->execute();

    $pago_id = $conexion->insert_id;

    // INSERTAR DETALLES
    foreach($detalles as $d){

        $compra_id = intval($d['compra_id']);
        $monto     = floatval($d['monto']);

        if($monto <= 0) continue;

        // CXP
        $stmt = $conexion->prepare("SELECT id_cxp, saldo FROM cxp WHERE compra_id=? AND activo=1 LIMIT 1");
        $stmt->bind_param("i",$compra_id);
        $stmt->execute();
        $cxp = $stmt->get_result()->fetch_assoc();

        if(!$cxp) throw new Exception("No existe cuenta por pagar para compra ".$compra_id);

        if($monto > $cxp["saldo"]) throw new Exception("Monto mayor al saldo disponible.");

        // Insertar detalle
        $stmt = $conexion->prepare("INSERT INTO pago_detalle (pago_id, compra_id, monto_aplicado, activo)
                                    VALUES (?, ?, ?, 1)");
        $stmt->bind_param("iid", $pago_id, $compra_id, $monto);
        $stmt->execute();

        // Actualizar CXP
        $nuevo = $cxp["saldo"] - $monto;

        $stmt = $conexion->prepare("UPDATE cxp SET saldo=? WHERE id_cxp=?");
        $stmt->bind_param("di", $nuevo, $cxp["id_cxp"]);
        $stmt->execute();

    }

    echo json_encode([
        "status"=>true,
        "msg"=>"Pago registrado correctamente",
        "pago_id"=>$pago_id
    ]);

}catch(Exception $e){
    echo json_encode(["status"=>false,"msg"=>$e->getMessage()]);
}

break;


// DEFAULT
default:
    echo json_encode(["success"=>false,"message"=>"Acción no válida"]);
}

?>
