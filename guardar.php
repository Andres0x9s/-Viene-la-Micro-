<?php
include("conexion.php");

$id_bus = $_POST["id_bus"];
$lat = $_POST["lat"];
$lng = $_POST["lng"];

$sql = "
IF EXISTS (SELECT 1 FROM bus_estado WHERE id_bus = ?)
    UPDATE bus_estado
    SET lat = ?, lng = ?, fecha = GETDATE()
    WHERE id_bus = ?
ELSE
    INSERT INTO bus_estado (id_bus, lat, lng, fecha)
    VALUES (?, ?, ?, GETDATE())
";

$params = [
    $id_bus,
    $lat,
    $lng,
    $id_bus,
    $id_bus,
    $lat,
    $lng
];

sqlsrv_query($conn, $sql, $params);

echo "OK";
?>