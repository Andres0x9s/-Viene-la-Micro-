<?php
include("../conexion.php");

date_default_timezone_set("America/Santiago");

$horaActual = new DateTime();

// 1. Traer todos los horarios ordenados
$sql = "SELECT 
            b.patente,
            r.nombre_ruta,
            h.hora_salida
        FROM horarios h
        INNER JOIN buses b ON h.id_bus = b.id_bus
        INNER JOIN rutas r ON b.id_ruta = r.id_ruta
        ORDER BY h.hora_salida";

$resultado = sqlsrv_query($conn, $sql);

$proximo = null;

// 2. Buscar el primero que sea mayor a la hora actual
while($row = sqlsrv_fetch_array($resultado, SQLSRV_FETCH_ASSOC)){

    $horaBus = DateTime::createFromFormat(
        'H:i:s',
        $row["hora_salida"]->format('H:i:s')
    );

    if($horaBus >= $horaActual){
        $proximo = [
            "patente" => $row["patente"],
            "ruta" => $row["nombre_ruta"],
            "hora" => $horaBus
        ];
        break;
    }
}

// 3. Si no hay, tomar el primero del día (mañana)
if(!$proximo){

    sqlsrv_free_stmt($resultado);

    $sql2 = "SELECT TOP 1
                b.patente,
                r.nombre_ruta,
                h.hora_salida
            FROM horarios h
            INNER JOIN buses b ON h.id_bus = b.id_bus
            INNER JOIN rutas r ON b.id_ruta = r.id_ruta
            ORDER BY h.hora_salida";

    $resultado = sqlsrv_query($conn, $sql2);
    $row = sqlsrv_fetch_array($resultado, SQLSRV_FETCH_ASSOC);

    $horaBus = DateTime::createFromFormat(
        'H:i:s',
        $row["hora_salida"]->format('H:i:s')
    );

    $proximo = [
        "patente" => $row["patente"],
        "ruta" => $row["nombre_ruta"],
        "hora" => $horaBus,
        "dia" => "mañana"
    ];
}
?>

<h2>🚍 Próximo Bus</h2>

<h3>🚌 <?= $proximo["patente"] ?> (<?= $proximo["ruta"] ?>)</h3>

<p>⏰ Sale a las: <?= $proximo["hora"]->format('H:i') ?></p>

<?php if(isset($proximo["dia"])) { ?>
    <p>📅 Este bus es para mañana</p>
<?php } ?>