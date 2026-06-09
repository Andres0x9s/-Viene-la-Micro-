<?php

$server = "ANDRESASUS\SQLEXPRESS";

$connectionInfo = array(
    "Database" => "emprendedoresdb",
    "UID" => "sa",
    "PWD" => "12345",
    "TrustServerCertificate" => true,
    "CharacterSet" => "UTF-8"
);

$conn = sqlsrv_connect($server, $connectionInfo);

if($conn === false){
    if (defined("JSON_ENDPOINT")) {
        return;
    }

    die(print_r(sqlsrv_errors(), true));
}


?>


