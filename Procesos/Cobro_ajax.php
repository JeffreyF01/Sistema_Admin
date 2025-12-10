<?php
session_start();
require_once "../conexion.php";
header('Content-Type: application/json');

$session_user_id = $_SESSION['user_info']['id_usuarios'] ?? null;

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input) || empty($input)) $input = $_POST;

$accion = $input['accion'] ?? '';

function resp($ok, $payload = []) {
    echo json_encode(array_merge(['success' => $ok], $payload));
    exit;
}

try {

    // ============================
    // LISTAR FACTURAS DEL CLIENTE
    // ============================
    if ($accion === "facturasCliente") {
        $cliente_id = intval($input["cliente_id"] ?? 0);

        $sql = "SELECT 
                    f.id_facturas,
                    f.numero_documento,
                    DATE_FORMAT(f.fecha, '%Y-%m-%d') AS fecha,
                    f.total,
                    IFNULL((
                        SELECT SUM(d.total)
                        FROM devolucion d
                        WHERE d.factura_id = f.id_facturas AND d.activo = 1
                    ),0) AS monto_devuelto,
                    (
                        f.total - IFNULL((
                            SELECT SUM(d.total)
                            FROM devolucion d
                            WHERE d.factura_id = f.id_facturas AND d.activo = 1
                        ),0) - IFNULL((
                            SELECT SUM(cd.monto_aplicado)
                            FROM cobro_detalle cd
                            WHERE cd.factura_id = f.id_facturas AND cd.activo = 1
                        ),0)
                    ) AS pendiente
                FROM factura f
                WHERE f.cliente_id = ?
                  AND f.activo = 1
                  AND f.condicion_id = 1   -- Crédito
                  AND (
                      f.total - IFNULL((
                          SELECT SUM(d.total)
                          FROM devolucion d
                          WHERE d.factura_id = f.id_facturas AND d.activo = 1
                      ),0) - IFNULL((
                          SELECT SUM(cd.monto_aplicado)
                          FROM cobro_detalle cd
                          WHERE cd.factura_id = f.id_facturas AND cd.activo = 1
                      ),0)
                  ) > 0
                ORDER BY f.fecha ASC";

        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $cliente_id);
        $stmt->execute();

        $res = $stmt->get_result();
        $data = [];
        while ($row = $res->fetch_assoc()) $data[] = $row;

        resp(true, ["data" => $data]);
    }

    // ============================
    // LISTAR CLIENTES
    // ============================
    if ($accion === "listar_clientes") {
        $q = "SELECT id_clientes, nombre FROM cliente WHERE activo = 1 ORDER BY nombre";
        $res = $conexion->query($q);

        $out = [];
        while ($r = $res->fetch_assoc()) $out[] = $r;

        resp(true, ["data" => $out]);
    }

    // ============================
    // GENERAR NÚMERO DE COBRO
    // ============================
    if ($accion === "generar_numero") {
        // Obtener el número más alto de todos los cobros existentes
        $res = $conexion->query("SELECT MAX(CAST(SUBSTRING(numero_documento, 5) AS UNSIGNED)) as max_num FROM cobro WHERE numero_documento LIKE 'COB-%'");

        $n = 1;
        if ($res && $res->num_rows > 0) {
            $row = $res->fetch_assoc();
            $max_num = intval($row['max_num'] ?? 0);
            $n = $max_num + 1;
        }

        $nuevo = "COB-" . str_pad($n, 8, "0", STR_PAD_LEFT);

        resp(true, ["numero" => $nuevo]);
    }

    // ============================
    // LISTAR COBROS
    // ============================
    if ($accion === "listar") {
        $sql = "SELECT 
                    c.id_cobros,
                    c.numero_documento,
                    DATE_FORMAT(c.fecha, '%Y-%m-%d') AS fecha,
                    c.monto,
                    c.metodo_pago,
                    c.activo,
                    cl.nombre AS cliente_nombre,
                    u.nombre AS usuario_nombre
                FROM cobro c
                LEFT JOIN cliente cl ON cl.id_clientes = c.cliente_id
                LEFT JOIN usuario u ON u.id_usuarios = c.usuario_id
                ORDER BY c.id_cobros DESC";

        $res = $conexion->query($sql);

        $out = [];
        while ($r = $res->fetch_assoc()) $out[] = $r;

        resp(true, ["data" => $out]);
    }

    // ============================
    // REGISTRAR COBRO
    // ============================
    if ($accion === "registrar") {

        $cliente_id = intval($input['cliente_id'] ?? 0);
        $fecha = $input['fecha'] ?? null;
        $metodo = $input['metodo'] ?? "Efectivo";
        $nota = $input['nota'] ?? "";
        $usuario_id = intval($input['usuario_id'] ?? $session_user_id);
        $monto = floatval($input['monto'] ?? 0);
        $detalle = $input["detalle"] ?? [];

        if ($cliente_id <= 0) resp(false, ["message" => "Cliente no recibido"]);
        if (!$fecha) resp(false, ["message" => "Fecha no recibida"]);
        if (empty($detalle)) resp(false, ["message" => "No se recibió detalle de cobro"]);

        $conexion->begin_transaction();

        try {
            // generar número - obtener el número más alto de todos los cobros existentes
            $res = $conexion->query("SELECT MAX(CAST(SUBSTRING(numero_documento, 5) AS UNSIGNED)) as max_num FROM cobro WHERE numero_documento LIKE 'COB-%'");

            $num = 1;
            if ($res && $res->num_rows > 0) {
                $row = $res->fetch_assoc();
                $max_num = intval($row['max_num'] ?? 0);
                $num = $max_num + 1;
            }

            $numero_cobro = "COB-" . str_pad($num, 8, "0", STR_PAD_LEFT);

            // insertar encabezado
            $stmt = $conexion->prepare("
                INSERT INTO cobro 
                (numero_documento, cliente_id, usuario_id, fecha, monto, metodo_pago, activo)
                VALUES (?, ?, ?, ?, ?, ?, 1)
            ");

            $stmt->bind_param(
                "siisds",
                $numero_cobro,
                $cliente_id,
                $usuario_id,
                $fecha,
                $monto,
                $metodo
            );


            if (!$stmt->execute())
                throw new Exception("Error insert cobro: " . $stmt->error);

            $cobro_id = $conexion->insert_id;

            // insertar detalle
            $stmtDet = $conexion->prepare("
                INSERT INTO cobro_detalle (cobro_id, factura_id, monto_aplicado, activo)
                VALUES (?, ?, ?, 1)
            ");

            foreach ($detalle as $d) {
                $factura_id = intval($d["factura_id"]);
                $monto_aplicado = floatval($d["monto_aplicado"]);

                // verificar pendiente correcto (considerando devoluciones)
                $stmtChk = $conexion->prepare("
                    SELECT 
                        f.total - IFNULL((
                            SELECT SUM(d.total)
                            FROM devolucion d
                            WHERE d.factura_id = f.id_facturas AND d.activo = 1
                        ),0) - IFNULL((
                            SELECT SUM(cd.monto_aplicado)
                            FROM cobro_detalle cd
                            WHERE cd.factura_id = f.id_facturas AND cd.activo = 1
                        ),0) AS pendiente
                    FROM factura f
                    WHERE f.id_facturas = ?
                    LIMIT 1
                ");
                $stmtChk->bind_param("i", $factura_id);
                $stmtChk->execute();

                $saldo = $stmtChk->get_result()->fetch_assoc()["pendiente"] ?? 0;

                if ($monto_aplicado > $saldo)
                    throw new Exception("Monto mayor al pendiente en factura $factura_id");

                // insertar detalle
                $stmtDet->bind_param("iid", $cobro_id, $factura_id, $monto_aplicado);
                $stmtDet->execute();

                // Verificar si la factura está completamente pagada (considerando devoluciones)
                $stmtVerify = $conexion->prepare("
                    SELECT 
                        f.total - IFNULL((
                            SELECT SUM(d.total)
                            FROM devolucion d
                            WHERE d.factura_id = f.id_facturas AND d.activo = 1
                        ),0) - IFNULL((
                            SELECT SUM(cd.monto_aplicado)
                            FROM cobro_detalle cd
                            WHERE cd.factura_id = f.id_facturas AND cd.activo = 1
                        ),0) AS pendiente
                    FROM factura f
                    WHERE f.id_facturas = ?
                    LIMIT 1
                ");
                $stmtVerify->bind_param("i", $factura_id);
                $stmtVerify->execute();
                $resultVerify = $stmtVerify->get_result();
                $rowVerify = $resultVerify->fetch_assoc();
                $pendiente_actual = floatval($rowVerify["pendiente"] ?? 0);

                // Si el pendiente es <= 0, cambiar estado a finalizado
                if ($pendiente_actual <= 0) {
                    $stmtEstado = $conexion->prepare("UPDATE factura SET estado = 'finalizado' WHERE id_facturas = ?");
                    $stmtEstado->bind_param("i", $factura_id);
                    $stmtEstado->execute();
                }
            }

            $conexion->commit();
            resp(true, ["message" => "Cobro registrado correctamente", "cobro_id" => $cobro_id]);

        } catch (Exception $e) {
            $conexion->rollback();
            resp(false, ["message" => $e->getMessage()]);
        }
    }

    resp(false, ["message" => "Acción no válida"]);

} catch (Exception $ex) {
    resp(false, ["message" => $ex->getMessage()]);
}
