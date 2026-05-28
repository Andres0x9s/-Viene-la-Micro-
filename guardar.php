<?php

include("conexion.php");

$lat = $_POST["lat"];
$lng = $_POST["lng"];

$sql = "INSERT INTO ubicaciones(lat,lng)
VALUES(?, ?)";

$params = array($lat, $lng);

$stmt = sqlsrv_query($conn, $sql, $params);

if($stmt){
    echo "ok";
}else{
    print_r(sqlsrv_errors());
}

?>