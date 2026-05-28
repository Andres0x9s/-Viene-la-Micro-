<?php

include("conexion.php");

$id_bus = $_POST["id_bus"];
$lat = str_replace(',', '.', $_POST["lat"]);
$lng = str_replace(',', '.', $_POST["lng"]);

$sql = "
INSERT INTO ubicaciones(id_bus, lat, lng)
VALUES(?, ?, ?)
";

$params = array($id_bus, $lat, $lng);

$stmt = sqlsrv_query($conn, $sql, $params);

if($stmt){
    echo "ok";
}else{
    print_r(sqlsrv_errors());
}

?>