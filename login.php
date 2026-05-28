<?php

include("conexion.php");

$usuario = $_POST["usuario"];
$password = $_POST["password"];

$sql = "
SELECT *
FROM conductores
WHERE usuario = ?
AND password = ?
";

$params = array($usuario, $password);

$stmt = sqlsrv_query($conn, $sql, $params);

if($stmt === false){
    die(print_r(sqlsrv_errors(), true));
}

$user = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

if($user){

    $id_bus = $user["id_bus"];

    echo "<script>
        window.location.href='gps.php?id_bus=$id_bus';
    </script>";
    exit();

}else{

    echo "Usuario o contraseña incorrectos";

}

?>