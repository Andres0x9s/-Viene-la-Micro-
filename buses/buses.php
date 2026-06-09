<?php
include ("../conexion.php");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Menú de Buses</title>
    <link rel="stylesheet" href="../assets/css/buses.css">
</head>

<body>

<div class="container">

<h1>🚌 Menú de Buses y Recorridos</h1>

<?php

$sql = "SELECT 
            r.nombre_ruta,
            r.inicio,
            r.fin,
            b.patente,
            b.dueno_linea,
            h.hora_salida
        FROM buses b
        INNER JOIN rutas r ON b.id_ruta = r.id_ruta
        INNER JOIN horarios h ON b.id_bus = h.id_bus
        ORDER BY r.nombre_ruta, h.hora_salida";

$resultado = sqlsrv_query($conn, $sql);

if($resultado == false){
    die(print_r(sqlsrv_errors(), true));
}

while($row = sqlsrv_fetch_array($resultado, SQLSRV_FETCH_ASSOC)){

?>

    <div class="card">

        <div class="ruta">
            📍 <?= $row["nombre_ruta"] ?> 
            (<?= $row["inicio"] ?> → <?= $row["fin"] ?>)
        </div>

        <div class="info">🚌 Patente: <?= $row["patente"] ?></div>
        <div class="info">👤 Dueño: <?= $row["dueno_linea"] ?></div>
        <div class="info">
            ⏰ Hora salida: <?= $row["hora_salida"]->format('H:i') ?>
        </div>

        <button>Ver en mapa</button>

    </div>

<?php } ?>

</div>

</body>
</html>


