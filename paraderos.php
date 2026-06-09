<?php
define("JSON_ENDPOINT", true);

ob_start();
include("conexion.php");
$salidaConexion = trim(ob_get_clean());

header("Content-Type: application/json; charset=utf-8");

function responderJson($payload, $status = 200)
{
    http_response_code($status);
    $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);

    if ($json === false) {
        $json = json_encode([
            "error" => "No se pudo codificar la respuesta JSON"
        ]);
    }

    echo $json;
    exit;
}

if (!isset($conn) || $conn === false) {
    responderJson([
        "error" => "No se pudo conectar a la base de datos",
        "detalle" => $salidaConexion ?: sqlsrv_errors()
    ], 500);
}

$id_ruta = $_GET["id_ruta"] ?? null;

$sql = "SELECT
            p.id_paradero,
            p.id_ruta,
            COALESCE(r.nombre_ruta, CONCAT('Ruta ', p.id_ruta)) AS nombre_ruta,
            p.nombre,
            p.lat,
            p.lng
        FROM paraderos p
        LEFT JOIN rutas r ON p.id_ruta = r.id_ruta
        WHERE p.estado = 'activo'";

$params = [];

if ($id_ruta) {
    $sql .= " AND p.id_ruta = ?";
    $params[] = $id_ruta;
}

$sql .= " ORDER BY r.nombre_ruta, p.nombre";

$stmt = sqlsrv_query($conn, $sql, $params);

if (!$stmt) {
    responderJson([
        "error" => "Error en consulta SQL",
        "detalle" => sqlsrv_errors()
    ], 500);
}

$data = [];

while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $data[] = [
        "id_paradero" => $row["id_paradero"],
        "id_ruta" => $row["id_ruta"],
        "nombre_ruta" => $row["nombre_ruta"],
        "nombre" => $row["nombre"],
        "lat" => $row["lat"],
        "lng" => $row["lng"]
    ];
}

responderJson($data);
