<?php
session_start();
include("../conexion.php");

if (!isset($_SESSION["conductor_id"]) || !isset($_SESSION["id_bus"]) || !isset($_SESSION["id_viaje"])) {
    http_response_code(401);
    echo "No autorizado";
    exit;
}

$id_bus_sesion = (int) $_SESSION["id_bus"];
$id_viaje = (int) $_SESSION["id_viaje"];

$id_bus = $_POST["id_bus"] ?? null;
$lat = $_POST["lat"] ?? null;
$lng = $_POST["lng"] ?? null;
$velocidad = $_POST["velocidad"] ?? 0;

if (!$id_bus || !$lat || !$lng) {
    http_response_code(400);
    echo "Datos incompletos";
    exit;
}

if ((int)$id_bus !== $id_bus_sesion) {
    http_response_code(403);
    echo "Bus no autorizado";
    exit;
}

$lat = (float) str_replace(",", ".", $lat);
$lng = (float) str_replace(",", ".", $lng);
$velocidad = (float) str_replace(",", ".", $velocidad);

// Guarda la ubicación ligada al viaje activo.
$sql = "INSERT INTO ubicaciones 
        (id_bus, lat, lng, fecha, velocidad, estado, id_viaje)
        VALUES (?, ?, ?, GETDATE(), ?, 'activo', ?)";

$params = [
    $id_bus_sesion,
    $lat,
    $lng,
    $velocidad,
    $id_viaje
];

$stmt = sqlsrv_query($conn, $sql, $params);

if (!$stmt) {
    http_response_code(500);
    echo "Error al guardar ubicación";
    exit;
}

// Guarda el punto inicial del viaje apenas llega la primera ubicación.
$sqlInicio = "UPDATE viajes
              SET lat_inicio = COALESCE(lat_inicio, ?),
                  lng_inicio = COALESCE(lng_inicio, ?)
              WHERE id_viaje = ?
              AND estado = 'activo'";

sqlsrv_query($conn, $sqlInicio, [$lat, $lng, $id_viaje]);

echo "OK";
?>
