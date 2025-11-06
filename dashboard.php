<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: index.html");
    exit();
}
$usuario = $_SESSION['usuario'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Panel Administrativo</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
    body {
        background: #eef2f7;
        font-family: "Poppins", sans-serif;
        margin: 0;
    }

    .sidebar {
        width: 260px;
        height: 100vh;
        background: linear-gradient(180deg,#062c6d,#004aad);
        color: white;
        position: fixed;
        left: 0;
        top: 0;
        padding-top: 30px;
        box-shadow: 3px 0 15px rgba(0,0,0,0.2);
        transition: 0.3s;
    }

    .sidebar h3 {
        text-transform: uppercase;
        font-weight: 700;
    }

    .sidebar a {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 14px 25px;
        color: white;
        font-size: 16px;
        text-decoration: none;
        transition: 0.25s;
    }

    .sidebar a:hover {
        background: rgba(255,255,255,0.15);
        padding-left: 30px;
    }

    #mantenimientosMenu a {
        font-size: 14px;
        padding: 10px 40px;
        color: #dcdcdc;
    }

    #mantenimientosMenu a:hover {
        background: rgba(255,255,255,0.18);
        color: #fff;
    }

    .content {
        margin-left: 260px;
        padding: 30px;
        animation: fadeIn .7s;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    .welcome-box {
        background: white;
        padding: 25px;
        border-radius: 12px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .welcome-box i {
        font-size: 32px;
        color: #004aad;
    }

    .card-dashboard {
        padding: 25px;
        background: white;
        border-radius: 12px;
        text-align: center;
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        transition: 0.3s;
    }

    .card-dashboard:hover {
        transform: translateY(-6px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    }

    .card-dashboard i {
        font-size: 38px;
        margin-bottom: 12px;
        color: #004aad;
    }

</style>
</head>

<body>

<div class="sidebar">
    <h3 class="text-center mb-4"><i class="fa-solid fa-shield-halved"></i> Admin</h3>

    <a href="#" id="mantenimientosBtn"><i class="fa-solid fa-toolbox"></i> Mantenimientos</a>
<div id="mantenimientosMenu" style="display:none">
    <a href="Mantproductos.php">Productos</a>
    <a href="MantAlmacen.php">Almacenes</a>
    <a href="MantUbicacion.php">Ubicaciones</a>
    <a href="MantDepartamento.php">Departamentos</a>
    <a href="MantGrupo.php">Grupos</a>
    <a href="MantTiposMov.php">Tipos de movimiento</a>
</div>

    <a href="#"><i class="fa-solid fa-gears"></i> Procesos</a>
    <a href="#"><i class="fa-solid fa-search"></i> Consultas</a>
    <a href="#"><i class="fa-solid fa-chart-pie"></i> Reportes</a>
    <a href="logout.php" class="mt-2 text-danger"><i class="fa-solid fa-arrow-right-from-bracket"></i> Cerrar sesión</a>
</div>

<div class="content">

    <div class="welcome-box mb-4">
        <i class="fa-solid fa-user"></i>
        <div>
            <h3 class="m-0">¡Bienvenido <?php echo $usuario; ?>!</h3>
            <small>Panel administrativo del sistema</small>
        </div>
    </div>

    <div class="row g-4 mt-2">
        <div class="col-md-3">
            <div class="card-dashboard">
                <i class="fa-solid fa-toolbox"></i>
                <h5>Mantenimientos</h5>
                <p>Gestionar catálogos del sistema</p>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card-dashboard">
                <i class="fa-solid fa-gears"></i>
                <h5>Procesos</h5>
                <p>Operaciones de negocio</p>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card-dashboard">
                <i class="fa-solid fa-search"></i>
                <h5>Consultas</h5>
                <p>Consulta información del sistema</p>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card-dashboard">
                <i class="fa-solid fa-chart-pie"></i>
                <h5>Reportes</h5>
                <p>Estadísticas y reportes</p>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById("mantenimientosBtn").addEventListener("click", () => {
    let menu = document.getElementById("mantenimientosMenu");
    menu.style.display = (menu.style.display === "none") ? "block" : "none";
});
</script>

</body>
</html>
