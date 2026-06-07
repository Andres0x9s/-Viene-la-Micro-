<?php
session_start();
include("../conexion.php");

function distanciaKm($lat1, $lon1, $lat2, $lon2){
    $radio = 6371;

    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);

    $a = sin($dLat / 2) * sin($dLat / 2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
         sin($dLon / 2) * sin($dLon / 2);

    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

    return $radio * $c;
}

if (isset($_SESSION["id_viaje"])) {

    $id_viaje = $_SESSION["id_viaje"];

    $sqlPuntos = "SELECT lat, lng
                  FROM ubicaciones
                  WHERE id_viaje = ?
                  ORDER BY fecha";

    $stmtPuntos = sqlsrv_query($conn, $sqlPuntos, [$id_viaje]);

    $puntos = [];

    if ($stmtPuntos) {
        while ($row = sqlsrv_fetch_array($stmtPuntos, SQLSRV_FETCH_ASSOC)) {
            $puntos[] = [
                "lat" => floatval($row["lat"]),
                "lng" => floatval($row["lng"])
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

    $lat_fin = null;
    $lng_fin = null;

    if (count($puntos) > 0) {
        $ultimo = end($puntos);
        $lat_fin = $ultimo["lat"];
        $lng_fin = $ultimo["lng"];
    }

    $sqlCerrar = "UPDATE viajes
                  SET estado = 'finalizado',
                      hora_fin = GETDATE(),
                      lat_fin = ?,
                      lng_fin = ?,
                      kilometros_recorridos = ?,
                      observacion = 'Finalizado automáticamente al cerrar sesión'
                  WHERE id_viaje = ?
                  AND estado = 'activo'";

    sqlsrv_query($conn, $sqlCerrar, [
        $lat_fin,
        $lng_fin,
        round($totalKm, 2),
        $id_viaje
    ]);
}

session_unset();
session_destroy();

header("Location: login.php");
exit;
?>