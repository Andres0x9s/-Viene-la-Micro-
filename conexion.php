<?php

$serverName = "emprendedores-sql-server.database.windows.net";

$connectionOptions = array(
    "Database" => ".\\SQLEXPRESS",
    "Uid" => "sa",
    "PWD" => "12345",
    "Encrypt" => true,
    "TrustServerCertificate" => false
);



$conn = sqlsrv_connect($serverName, $connectionOptions);

if($conn === false){
    die(print_r(sqlsrv_errors(), true));
}

?>