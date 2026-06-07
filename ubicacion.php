<?php
include("conexion.php");

header("Content-Type: application/json; charset=utf-8");

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
    http_response_code(500);
    echo json_encode([
        "error" => "Error en consulta SQL",
        "detalle" => sqlsrv_errors()
    ]);
    exit;
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

echo json_encode($data);