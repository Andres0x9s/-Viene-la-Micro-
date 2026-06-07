<?php
session_start();
include("../conexion.php");

if (!isset($_SESSION["conductor_id"]) || !isset($_SESSION["id_bus"]) || !isset($_SESSION["id_viaje"])) {
    header("Location: login.php");
    exit;
}

$id_viaje = (int) $_SESSION["id_viaje"];
$id_bus = (int) $_SESSION["id_bus"];

function distanciaKm($lat1, $lon1, $lat2, $lon2) {
    $radioTierraKm = 6371;

    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);

    $a = sin($dLat / 2) * sin($dLat / 2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
         sin($dLon / 2) * sin($dLon / 2);

    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

    return $radioTierraKm * $c;
}

// Obtener puntos GPS del viaje en orden.
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

// Finalizar viaje y guardar kilómetros.
$sqlFinalizar = "UPDATE viajes
                 SET estado = 'finalizado',
                     hora_fin = GETDATE(),
                     lat_fin = ?,
                     lng_fin = ?,
                     kilometros_recorridos = ?
                 WHERE id_viaje = ?
                 AND id_bus = ?
                 AND estado = 'activo'";

$stmtFinalizar = sqlsrv_query($conn, $sqlFinalizar, [
    $latFin,
    $lngFin,
    $totalKm,
    $id_viaje,
    $id_bus
]);

// Limpiar datos del viaje, pero mantener sesión del conductor para elegir otro bus si quiere.
unset($_SESSION["id_bus"]);
unset($_SESSION["id_viaje"]);
unset($_SESSION["direccion"]);

$_SESSION["viaje_finalizado_msg"] = "Viaje finalizado correctamente. Kilómetros recorridos: " . number_format($totalKm, 2, ',', '.') . " km.";

header("Location: seleccionar_bus.php");
exit;
?>
