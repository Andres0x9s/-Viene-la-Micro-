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

$sql = "
SELECT 
    b.id_bus,
    b.patente,
    b.dueno_linea,
    b.id_ruta,
    u.lat,
    u.lng,
    u.fecha,
    u.velocidad
FROM buses b
INNER JOIN (
    SELECT 
        id_bus,
        MAX(fecha) AS ultima_fecha
    FROM ubicaciones
    GROUP BY id_bus
) ult ON b.id_bus = ult.id_bus
INNER JOIN ubicaciones u 
    ON u.id_bus = ult.id_bus 
    AND u.fecha = ult.ultima_fecha
WHERE DATEDIFF(MINUTE, u.fecha, GETDATE()) <= 5
";

$params = [];

if ($id_ruta) {
    $sql .= " AND b.id_ruta = ?";
    $params[] = $id_ruta;
}

$sql .= " ORDER BY u.fecha DESC";

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
        "id_bus" => $row["id_bus"],
        "patente" => $row["patente"],
        "dueno_linea" => $row["dueno_linea"],
        "id_ruta" => $row["id_ruta"],
        "lat" => $row["lat"],
        "lng" => $row["lng"],
        "fecha" => $row["fecha"] ? $row["fecha"]->format("Y-m-d H:i:s") : null,
        "velocidad" => $row["velocidad"]
    ];
}

responderJson($data);
