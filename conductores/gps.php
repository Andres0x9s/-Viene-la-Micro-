<?php
session_start();

if (!isset($_SESSION["conductor_id"])) {
    header("Location: login.php");
    exit;
}

if (!isset($_SESSION["id_bus"]) || !isset($_SESSION["id_viaje"])) {
    header("Location: seleccionar_bus.php");
    exit;
}

$id_bus = $_SESSION["id_bus"];
$id_viaje = $_SESSION["id_viaje"];
$direccion = $_SESSION["direccion"] ?? "Sin dirección";
$usuario = $_SESSION["conductor_usuario"] ?? "Conductor";
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>GPS Conductor - Viene La Micro</title>

<link rel="icon" type="image/png" href="../busicono.png">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/gps.css">
</head>

<body>

<div class="gps-card">
    <div class="status">
        <span class="dot"></span>
        GPS ACTIVO
    </div>

    <h1>Hola, <?= htmlspecialchars($usuario) ?></h1>

    <div class="subtitle">
        Tu ubicación se está enviando automáticamente al sistema.
    </div>

    <div class="info-grid">
        <div class="box">
            <small>Bus seleccionado</small>
            <strong>ID <?= htmlspecialchars($id_bus) ?></strong>
        </div>

        <div class="box">
            <small>Viaje activo</small>
            <strong>#<?= htmlspecialchars($id_viaje) ?></strong>
        </div>

        <div class="box">
            <small>Dirección</small>
            <strong class="direction-value"><?= htmlspecialchars($direccion) ?></strong>
        </div>

        <div class="box">
            <small>Última actualización</small>
            <strong id="hora">--:--:--</strong>
        </div>

        <div class="box">
            <small>Latitud</small>
            <strong id="lat">---</strong>
        </div>

        <div class="box">
            <small>Longitud</small>
            <strong id="lng">---</strong>
        </div>
    </div>

    <div class="message" id="estado">
        Solicitando permiso de ubicación...
    </div>

    <div class="actions-row">
        <a href="finalizar_viaje.php" class="logout finish" onclick="return confirm('¿Finalizar este viaje? Se calcularán los kilómetros recorridos.');">
            Finalizar viaje
        </a>
        <a href="logout.php" class="logout" onclick="return confirm('Si cierras sesión sin finalizar, el viaje quedará activo. ¿Continuar?');">
            Cerrar sesión
        </a>
    </div>
</div>

<script>
const id_bus = <?= json_encode($id_bus) ?>;
const id_viaje = <?= json_encode($id_viaje) ?>;

const estado = document.getElementById("estado");
const latBox = document.getElementById("lat");
const lngBox = document.getElementById("lng");
const horaBox = document.getElementById("hora");

function actualizarHora(){
    const ahora = new Date();
    horaBox.textContent = ahora.toLocaleTimeString("es-CL");
}

function enviarUbicacion(lat, lng, velocidad){
    const datos = new URLSearchParams();
    datos.append("id_bus", id_bus);
    datos.append("lat", lat);
    datos.append("lng", lng);
    datos.append("velocidad", velocidad ?? 0);

    fetch("guardar.php", {
        method:"POST",
        headers:{
            "Content-Type":"application/x-www-form-urlencoded"
        },
        body:datos.toString()
    })
    .then(res => {
        if(!res.ok){
            throw new Error("Error al guardar ubicación");
        }
        return res.text();
    })
    .then(respuesta => {
        latBox.textContent = Number(lat).toFixed(5);
        lngBox.textContent = Number(lng).toFixed(5);
        actualizarHora();
        estado.textContent = "Ubicación enviada correctamente.";
    })
    .catch(() => {
        estado.textContent = "Error al enviar ubicación.";
    });
}

if(navigator.geolocation){
    navigator.geolocation.watchPosition(
        pos => {
            const lat = pos.coords.latitude;
            const lng = pos.coords.longitude;
            const velocidad = pos.coords.speed ? pos.coords.speed * 3.6 : 0;

            enviarUbicacion(lat, lng, velocidad);
        },
        error => {
            estado.textContent = "No se pudo obtener la ubicación. Revisa los permisos GPS.";
        },
        {
            enableHighAccuracy:true,
            maximumAge:0,
            timeout:10000
        }
    );
}else{
    estado.textContent = "Este navegador no soporta geolocalización.";
}
</script>

</body>
</html>


