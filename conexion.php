<?php

$server = "database-pepa.cc3wbiv3rviq.us-east-1.rds.amazonaws.com,1433";

$connectionInfo = array(
    "Database" => "emprendedoresdb",
    "UID" => "admin",
    "PWD" => "12345678",
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


