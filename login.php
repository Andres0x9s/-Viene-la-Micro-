
<?php

session_start();

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

$user = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

if($user){

    $_SESSION["id_bus"] = $user["id_bus"];

    header("Location: gps.php");

}else{

    echo "Usuario incorrecto";

}

?>

