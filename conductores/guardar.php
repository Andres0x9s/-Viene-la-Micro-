<?php
session_start();
include("../conexion.php");

if (!isset($_SESSION["conductor_id"]) || !isset($_SESSION["id_bus"])) {
    http_response_code(401);
    echo "No autorizado";
    exit;
}

$id_bus_sesion = $_SESSION["id_bus"];

$id_bus = $_POST["id_bus"] ?? null;
$lat = $_POST["lat"] ?? null;
$lng = $_POST["lng"] ?? null;
$velocidad = $_POST["velocidad"] ?? 0;

if (!$id_bus || !$lat || !$lng) {
    http_response_code(400);
    echo "Datos incompletos";
    exit;
}

if ((int)$id_bus !== (int)$id_bus_sesion) {
    http_response_code(403);
    echo "Bus no autorizado";
    exit;
}

$lat = str_replace(",", ".", $lat);
$lng = str_replace(",", ".", $lng);
$velocidad = str_replace(",", ".", $velocidad);

$sql = "INSERT INTO ubicaciones 
        (id_bus, lat, lng, fecha, velocidad, estado, id_viaje)
        VALUES (?, ?, ?, GETDATE(), ?, 'activo', NULL)";

$params = [
    $id_bus_sesion,
    $lat,
    $lng,
    $velocidad
];

$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt) {
    echo "OK";
} else {
    http_response_code(500);
    echo "Error al guardar ubicación";
}
?>