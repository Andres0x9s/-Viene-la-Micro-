<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Recibir id del bus desde la URL
$id_bus = $_GET["id_bus"] ?? null;

if(!$id_bus){
    die("No se recibió id_bus");
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>GPS BUS</title>
</head>
<body>

<h1>GPS FUNCIONANDO</h1>

<h2>Bus: <?php echo $id_bus; ?></h2>

<script>

const id_bus = <?php echo $id_bus; ?>;

function enviar(lat, lng){

    console.log("ENVIANDO:", lat, lng);

    fetch("guardar.php", {

        method: "POST",

        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },

        body: `id_bus=${id_bus}&lat=${lat}&lng=${lng}`

    })
    .then(res => res.text())
    .then(data => console.log("RESPUESTA PHP:", data))
    .catch(err => console.log("ERROR FETCH:", err));

}

// GPS en tiempo real
if ("geolocation" in navigator) {

    navigator.geolocation.watchPosition(

        (posicion) => {

            let lat = posicion.coords.latitude;
            let lng = posicion.coords.longitude;

            console.log("GPS OK:", lat, lng);

            enviar(lat, lng);

        },

        (error) => {
            console.log("ERROR GPS:", error);
        },

        {
            enableHighAccuracy: true,
            maximumAge: 0,
            timeout: 10000
        }

    );

} else {
    console.log("GPS no soportado");
}

</script>

</body>
</html>

