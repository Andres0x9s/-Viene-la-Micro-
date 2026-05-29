<?php
include ("../conexion.php");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Menú de Buses</title>

    <style>
        body{
            font-family: Arial;
            background:#f4f4f4;
        }

        .container{
            width: 80%;
            margin: auto;
        }

        .card{
            background: white;
            padding: 15px;
            margin: 15px 0;
            border-radius: 10px;
            box-shadow: 0px 2px 5px rgba(0,0,0,0.2);
        }

        .ruta{
            font-size: 20px;
            font-weight: bold;
            color: #2c3e50;
        }

        .info{
            margin: 5px 0;
        }

        button{
            background: #3498db;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover{
            background: #2980b9;
        }
    </style>
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