<?php

$server = "ANDRESASUS\SQLEXPRESS";

$connectionInfo = array(
    "Database" => "emprendedoresdb",
    "UID" => "sa",
    "PWD" => "12345",
    "TrustServerCertificate" => true
);

$conn = sqlsrv_connect($server, $connectionInfo);

if($conn === false){
    die(print_r(sqlsrv_errors(), true));
}


?>


