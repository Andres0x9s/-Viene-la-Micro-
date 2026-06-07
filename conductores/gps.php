<?php
session_start();

if (!isset($_SESSION["conductor_id"]) || !isset($_SESSION["id_bus"])) {
    header("Location: login.php");
    exit;
}

$id_bus = $_SESSION["id_bus"];
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

<style>
*{box-sizing:border-box}

body{
    margin:0;
    min-height:100vh;
    display:flex;
    align-items:center;
    justify-content:center;
    background:#020617;
    color:white;
    font-family:"Poppins",sans-serif;
    overflow:hidden;
}

body::before{
    content:"";
    position:fixed;
    inset:0;
    background:
        radial-gradient(circle at 15% 20%, rgba(37,244,255,.22), transparent 30%),
        radial-gradient(circle at 85% 80%, rgba(0,200,232,.16), transparent 35%);
}

.gps-card{
    position:relative;
    z-index:2;
    width:92%;
    max-width:520px;
    padding:36px;
    border-radius:32px;
    background:rgba(15,23,42,.78);
    border:1px solid rgba(255,255,255,.1);
    backdrop-filter:blur(20px);
    box-shadow:0 25px 80px rgba(0,0,0,.5);
}

.status{
    display:inline-flex;
    align-items:center;
    gap:10px;
    padding:8px 14px;
    border-radius:999px;
    background:rgba(34,197,94,.12);
    border:1px solid rgba(34,197,94,.35);
    color:#86efac;
    font-size:12px;
    font-weight:900;
    margin-bottom:20px;
}

.dot{
    width:9px;
    height:9px;
    border-radius:50%;
    background:#22c55e;
    box-shadow:0 0 18px #22c55e;
}

h1{
    margin:0 0 8px;
    font-size:34px;
}

.subtitle{
    color:#94a3b8;
    margin-bottom:28px;
}

.info-grid{
    display:grid;
    grid-template-columns:repeat(2,1fr);
    gap:14px;
    margin-bottom:24px;
}

.box{
    background:rgba(255,255,255,.07);
    border:1px solid rgba(255,255,255,.1);
    border-radius:20px;
    padding:18px;
}

.box small{
    color:#94a3b8;
    display:block;
    margin-bottom:8px;
}

.box strong{
    font-size:20px;
}

.message{
    padding:16px;
    border-radius:18px;
    background:rgba(37,244,255,.1);
    border:1px solid rgba(37,244,255,.25);
    color:#cffafe;
    font-size:14px;
    line-height:1.6;
}

.logout{
    display:block;
    text-align:center;
    margin-top:22px;
    padding:14px;
    border-radius:999px;
    color:white;
    text-decoration:none;
    background:rgba(255,255,255,.08);
    border:1px solid rgba(255,255,255,.1);
    font-weight:800;
}

.logout:hover{
    border-color:rgba(37,244,255,.45);
    color:#25f4ff;
}

@media(max-width:600px){
    .info-grid{
        grid-template-columns:1fr;
    }
}
</style>
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
            <small>Bus asignado</small>
            <strong>ID <?= htmlspecialchars($id_bus) ?></strong>
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

    <a href="logout.php" class="logout">Cerrar sesión</a>
</div>

<script>
const id_bus = <?= json_encode($id_bus) ?>;

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
    .then(res => res.text())
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