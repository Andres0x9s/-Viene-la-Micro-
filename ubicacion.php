<?php

include("conexion.php");

header('Content-Type: application/json');

$sql = "
SELECT id_bus, lat, lng
FROM ubicaciones
";

$stmt = sqlsrv_query($conn, $sql);

$buses = [];

while($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)){

    $buses[] = array(
        "id_bus" => $row["id_bus"],
        "lat" => $row["lat"],
        "lng" => $row["lng"]
    );

}

echo json_encode($buses);

?>