<?php
include("conexion.php");

header('Content-Type: application/json');

$id_ruta = $_GET["id_ruta"] ?? null;

$sql = "
SELECT 
    u.id_bus,
    u.lat,
    u.lng,
    b.patente,
    b.dueno_linea,
    b.id_ruta
FROM bus_estado u
INNER JOIN buses b ON u.id_bus = b.id_bus
";

$params = [];

if($id_ruta){
    $sql .= " WHERE b.id_ruta = ?";
    $params[] = $id_ruta;
}

$stmt = sqlsrv_query($conn, $sql, $params);

if($stmt === false){
    die(json_encode(sqlsrv_errors()));
}

$buses = [];

while($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)){
    $buses[] = $row;
}

echo json_encode($buses);
?>