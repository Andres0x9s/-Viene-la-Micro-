<?php

session_start();

include("../conexion.php");

$usuario = $_POST["usuario"];
$password = $_POST["password"];

$sql = "
SELECT *
FROM administradores
WHERE usuario = ?
AND password = ?
";

$params = [$usuario, $password];

$stmt = sqlsrv_query($conn, $sql, $params);

$admin = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

if($admin){

    $_SESSION["admin"] = $admin["usuario"];
    $_SESSION["id_ruta"] = $admin["id_ruta"];

    header("Location: dashboard.php");
    exit();

}else{

    echo "Credenciales incorrectas";

}