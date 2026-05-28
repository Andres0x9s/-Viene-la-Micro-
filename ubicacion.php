<?php

include("conexion.php");

$sql = "
SELECT TOP 1 *
FROM ubicaciones
ORDER BY id DESC
";

$stmt = sqlsrv_query($conn, $sql);

$data = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

echo json_encode($data);

?>