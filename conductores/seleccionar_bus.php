<?php
session_start();
include("../conexion.php");

if (!isset($_SESSION["conductor_id"]) || !isset($_SESSION["id_ruta"])) {
    header("Location: login.php");
    exit;
}

$id_conductor = $_SESSION["conductor_id"];
$id_ruta = $_SESSION["id_ruta"];
$error = "";
$mensaje = $_SESSION["viaje_finalizado_msg"] ?? "";
unset($_SESSION["viaje_finalizado_msg"]);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id_bus = $_POST["id_bus"] ?? null;
    $direccion = $_POST["direccion"] ?? "";

    if (!$id_bus || !$direccion) {
        $error = "Debes seleccionar un bus y una dirección.";
    } else {

        $sqlValidarBus = "SELECT id_bus 
                          FROM buses 
                          WHERE id_bus = ? 
                          AND id_ruta = ? 
                          AND estado = 'activo'";

        $stmtValidarBus = sqlsrv_query($conn, $sqlValidarBus, [$id_bus, $id_ruta]);

        $sqlValidarDireccion = "SELECT DISTINCT h.direccion
                                FROM horarios h
                                INNER JOIN buses b ON h.id_bus = b.id_bus
                                WHERE b.id_ruta = ?
                                AND h.direccion = ?";

        $stmtValidarDireccion = sqlsrv_query($conn, $sqlValidarDireccion, [$id_ruta, $direccion]);

        if (
            $stmtValidarBus &&
            sqlsrv_fetch_array($stmtValidarBus, SQLSRV_FETCH_ASSOC) &&
            $stmtValidarDireccion &&
            sqlsrv_fetch_array($stmtValidarDireccion, SQLSRV_FETCH_ASSOC)
        ) {

            $sqlViaje = "INSERT INTO viajes 
                         (id_bus, id_conductor, id_ruta, direccion, hora_inicio, estado)
                         OUTPUT INSERTED.id_viaje
                         VALUES (?, ?, ?, ?, GETDATE(), 'activo')";

            $stmtViaje = sqlsrv_query($conn, $sqlViaje, [
                $id_bus,
                $id_conductor,
                $id_ruta,
                $direccion
            ]);

            if ($stmtViaje && $viaje = sqlsrv_fetch_array($stmtViaje, SQLSRV_FETCH_ASSOC)) {
                $_SESSION["id_bus"] = $id_bus;
                $_SESSION["id_viaje"] = $viaje["id_viaje"];
                $_SESSION["direccion"] = $direccion;

                header("Location: gps.php");
                exit;
            } else {
                $error = "No se pudo crear el viaje.";
            }

        } else {
            $error = "El bus o la dirección seleccionada no corresponde a tu línea.";
        }
    }
}

$sqlBuses = "SELECT id_bus, patente, dueno_linea
             FROM buses
             WHERE id_ruta = ?
             AND estado = 'activo'
             ORDER BY patente";

$stmtBuses = sqlsrv_query($conn, $sqlBuses, [$id_ruta]);

$sqlDirecciones = "SELECT DISTINCT h.direccion
                   FROM horarios h
                   INNER JOIN buses b ON h.id_bus = b.id_bus
                   WHERE b.id_ruta = ?
                   ORDER BY h.direccion";

$stmtDirecciones = sqlsrv_query($conn, $sqlDirecciones, [$id_ruta]);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Seleccionar Bus</title>

<style>
body{
    margin:0;
    min-height:100vh;
    display:flex;
    align-items:center;
    justify-content:center;
    background:#020617;
    color:white;
    font-family:Arial, sans-serif;
}

.card{
    width:90%;
    max-width:480px;
    background:#0f172a;
    padding:32px;
    border-radius:24px;
    border:1px solid rgba(255,255,255,.1);
}

select,button{
    width:100%;
    padding:14px;
    margin-top:12px;
    border-radius:14px;
    border:none;
}

select{
    background:#1e293b;
    color:white;
}

button{
    background:#25f4ff;
    color:#020617;
    font-weight:bold;
    cursor:pointer;
}

.success{
    color:#bbf7d0;
    background:rgba(34,197,94,.15);
    padding:12px;
    border-radius:12px;
    margin-bottom:12px;
}

.error{
    color:#fecaca;
    background:rgba(239,68,68,.15);
    padding:12px;
    border-radius:12px;
    margin-bottom:12px;
}

a{
    color:#94a3b8;
}
</style>
</head>

<body>

<div class="card">
    <h1>Seleccionar bus</h1>
    <p>Elige el bus y la dirección disponible para tu línea.</p>

    <?php if($mensaje): ?>
        <div class="success"><?= htmlspecialchars($mensaje) ?></div>
    <?php endif; ?>

    <?php if($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <select name="id_bus" required>
            <option value="">Selecciona un bus</option>

            <?php while($bus = sqlsrv_fetch_array($stmtBuses, SQLSRV_FETCH_ASSOC)): ?>
                <option value="<?= htmlspecialchars($bus["id_bus"]) ?>">
                    <?= htmlspecialchars($bus["patente"]) ?> - <?= htmlspecialchars($bus["dueno_linea"]) ?>
                </option>
            <?php endwhile; ?>
        </select>

        <select name="direccion" required>
            <option value="">Selecciona dirección</option>

            <?php while($dir = sqlsrv_fetch_array($stmtDirecciones, SQLSRV_FETCH_ASSOC)): ?>
                <option value="<?= htmlspecialchars($dir["direccion"]) ?>">
                    <?= htmlspecialchars($dir["direccion"]) ?>
                </option>
            <?php endwhile; ?>
        </select>

        <button type="submit">Iniciar viaje</button>
    </form>

    <br>
    <a href="logout.php">Cerrar sesión</a>
</div>

</body>
</html>