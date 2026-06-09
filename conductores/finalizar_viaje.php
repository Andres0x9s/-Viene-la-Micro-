<?php
session_start();
include("../conexion.php");

if (!isset($_SESSION["conductor_id"]) || !isset($_SESSION["id_bus"]) || !isset($_SESSION["id_viaje"])) {
    header("Location: login.php");
    exit;
}

$id_viaje = (int) $_SESSION["id_viaje"];
$id_bus = (int) $_SESSION["id_bus"];
$direccion = $_SESSION["direccion"] ?? "Sin dirección";
$usuario = $_SESSION["conductor_usuario"] ?? "Conductor";
$error = "";

function h($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
}

function largoTexto($value)
{
    if (function_exists("mb_strlen")) {
        return mb_strlen($value, "UTF-8");
    }

    return strlen($value);
}

function distanciaKm($lat1, $lon1, $lat2, $lon2)
{
    $radioTierraKm = 6371;

    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);

    $a = sin($dLat / 2) * sin($dLat / 2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
         sin($dLon / 2) * sin($dLon / 2);

    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

    return $radioTierraKm * $c;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $observacion = trim($_POST["observacion"] ?? "");

    if (largoTexto($observacion) > 500) {
        $error = "La observación no puede superar los 500 caracteres.";
    } else {
        $sqlPuntos = "SELECT lat, lng
                      FROM ubicaciones
                      WHERE id_viaje = ?
                      ORDER BY fecha ASC";

        $stmtPuntos = sqlsrv_query($conn, $sqlPuntos, [$id_viaje]);
        $puntos = [];

        if ($stmtPuntos) {
            while ($row = sqlsrv_fetch_array($stmtPuntos, SQLSRV_FETCH_ASSOC)) {
                $puntos[] = [
                    "lat" => (float) $row["lat"],
                    "lng" => (float) $row["lng"]
                ];
            }
        }

        $totalKm = 0;

        for ($i = 0; $i < count($puntos) - 1; $i++) {
            $totalKm += distanciaKm(
                $puntos[$i]["lat"],
                $puntos[$i]["lng"],
                $puntos[$i + 1]["lat"],
                $puntos[$i + 1]["lng"]
            );
        }

        $totalKm = round($totalKm, 2);
        $latFin = null;
        $lngFin = null;

        if (count($puntos) > 0) {
            $ultimo = $puntos[count($puntos) - 1];
            $latFin = $ultimo["lat"];
            $lngFin = $ultimo["lng"];
        }

        $sqlFinalizar = "UPDATE viajes
                         SET estado = 'finalizado',
                             hora_fin = GETDATE(),
                             lat_fin = ?,
                             lng_fin = ?,
                             kilometros_recorridos = ?,
                             observacion = ?
                         WHERE id_viaje = ?
                         AND id_bus = ?
                         AND estado = 'activo'";

        $stmtFinalizar = sqlsrv_query($conn, $sqlFinalizar, [
            $latFin,
            $lngFin,
            $totalKm,
            $observacion !== "" ? $observacion : "Sin observaciones",
            $id_viaje,
            $id_bus
        ]);

        if ($stmtFinalizar) {
            unset($_SESSION["id_bus"], $_SESSION["id_viaje"], $_SESSION["direccion"]);
            $_SESSION["viaje_finalizado_msg"] = "Viaje finalizado correctamente. Kilómetros recorridos: " . number_format($totalKm, 2, ",", ".") . " km.";

            header("Location: seleccionar_bus.php");
            exit;
        }

        $detalleSql = sqlsrv_errors();
        $error = "No se pudo finalizar el viaje. Intenta nuevamente.";

        if (!empty($detalleSql[0]["message"])) {
            $error .= " Detalle: " . $detalleSql[0]["message"];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Finalizar viaje - Viene La Micro</title>
<link rel="icon" type="image/png" href="../busicono.png">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/gps.css">
</head>

<body>
<div class="gps-card">
    <div class="status">
        <span class="dot"></span>
        CIERRE DE VIAJE
    </div>

    <h1>Finalizar viaje</h1>

    <div class="subtitle">
        <?= h($usuario) ?>, agrega una observación breve si ocurrió algo relevante en la ruta.
    </div>

    <div class="info-grid">
        <div class="box">
            <small>Bus</small>
            <strong>ID <?= h($id_bus) ?></strong>
        </div>

        <div class="box">
            <small>Viaje</small>
            <strong>#<?= h($id_viaje) ?></strong>
        </div>

        <div class="box">
            <small>Dirección</small>
            <strong class="direction-value"><?= h($direccion) ?></strong>
        </div>
    </div>

    <?php if ($error): ?>
        <div class="message error-message"><?= h($error) ?></div>
    <?php endif; ?>

    <form class="finish-form" method="POST">
        <label for="observacion">Observación del viaje</label>
        <textarea id="observacion" name="observacion" maxlength="500" placeholder="Ej: tráfico alto, desvío, pasajero asistido, problema mecánico o sin novedades."></textarea>
        <small>Máximo 500 caracteres.</small>

        <div class="actions-row">
            <button class="logout finish" type="submit">
                Finalizar viaje
            </button>
            <a href="gps.php" class="logout">
                Volver al GPS
            </a>
        </div>
    </form>
</div>
</body>
</html>
