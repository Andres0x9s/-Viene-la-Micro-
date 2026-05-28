
<?php

session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

if(!isset($_SESSION["id_bus"])){

    die("No hay sesión iniciada");

}

$id_bus = $_SESSION["id_bus"];

?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>GPS BUS</title>
</head>
<body>

<h1>GPS FUNCIONANDO</h1>

<h2>
Bus:
<?php echo $id_bus; ?>
</h2>

<script>

const id_bus = <?php echo $id_bus; ?>;

function enviar(lat, lng){

    fetch("guardar.php", {

        method:"POST",

        headers:{
            "Content-Type":"application/x-www-form-urlencoded"
        },

        body:`id_bus=${id_bus}&lat=${lat}&lng=${lng}`

    });

}

navigator.geolocation.watchPosition((posicion)=>{

    let lat = posicion.coords.latitude;
    let lng = posicion.coords.longitude;

    enviar(lat, lng);

});

</script>

</body>
</html>

