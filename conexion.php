<?php

$serverName = "TU_SERVIDOR.database.windows.net";

$connectionOptions = array(
    "Database" => "TU_DATABASE",
    "Uid" => "TU_USUARIO",
    "PWD" => "TU_PASSWORD",
    "Encrypt" => true,
    "TrustServerCertificate" => false
);

$conn = sqlsrv_connect($serverName, $connectionOptions);

if($conn === false){
    die(print_r(sqlsrv_errors(), true));
}

?>