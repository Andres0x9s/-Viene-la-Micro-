<?php
include("auth.php");
include("../conexion.php");
include("../helpers/passwords.php");

$idRuta = (int) $_SESSION["id_ruta"];
$adminUsuario = $_SESSION["admin"] ?? "Administrador";
$section = $_GET["section"] ?? "resumen";
$validSections = ["resumen", "buses", "conductores", "horarios", "paraderos", "viajes", "online", "detalle_viaje"];

if (!in_array($section, $validSections, true)) {
    $section = "resumen";
}

function h($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
}

function fetchRows($stmt)
{
    $rows = [];

    if (!$stmt) {
        return $rows;
    }

    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $rows[] = $row;
    }

    return $rows;
}

function firstValue($conn, $sql, $params = [])
{
    $stmt = sqlsrv_query($conn, $sql, $params);

    if (!$stmt) {
        return 0;
    }

    $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_NUMERIC);
    return $row ? ($row[0] ?? 0) : 0;
}

function formatTimeValue($value)
{
    if ($value instanceof DateTimeInterface) {
        return $value->format("H:i");
    }

    return substr((string) $value, 0, 5);
}

function formatDateTimeValue($value)
{
    if ($value instanceof DateTimeInterface) {
        return $value->format("d-m-Y H:i");
    }

    return $value ? (string) $value : "-";
}

function formatDateValue($value)
{
    if ($value instanceof DateTimeInterface) {
        return $value->format("d-m-Y");
    }

    return $value ? (string) $value : "-";
}

function flash($type, $message)
{
    $_SESSION["admin_flash"] = [
        "type" => $type,
        "message" => $message
    ];
}

function redirectSection($section)
{
    header("Location: dashboard.php?section=" . urlencode($section));
    exit;
}

function normalizePatente($value)
{
    return strtoupper(trim((string) $value));
}

function validPatente($value)
{
    return (bool) preg_match("/^[A-Z0-9 -]{4,12}$/", normalizePatente($value));
}

function validCoordinate($value, $min, $max)
{
    if (!is_numeric($value)) {
        return false;
    }

    $number = (float) $value;
    return $number >= $min && $number <= $max;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";

    if ($action === "create_bus") {
        $patente = normalizePatente($_POST["patente"] ?? "");
        $dueno = trim($_POST["dueno_linea"] ?? "");
        $estado = $_POST["estado"] ?? "activo";

        if (!validPatente($patente)) {
            flash("error", "La patente debe tener entre 4 y 12 caracteres, usando letras, números, espacios o guiones.");
            redirectSection("buses");
        }

        if ($dueno === "") {
            flash("error", "Debes ingresar el dueño o línea del bus.");
            redirectSection("buses");
        }

        $existe = firstValue($conn, "SELECT COUNT(*) FROM buses WHERE patente = ? AND id_ruta = ?", [$patente, $idRuta]);

        if ($existe > 0) {
            flash("error", "Ya existe un bus con esa patente en tu ruta.");
            redirectSection("buses");
        }

        $stmt = sqlsrv_query(
            $conn,
            "INSERT INTO buses (patente, dueno_linea, estado, id_ruta) VALUES (?, ?, ?, ?)",
            [$patente, $dueno, $estado, $idRuta]
        );

        flash($stmt ? "success" : "error", $stmt ? "Bus creado correctamente." : "No se pudo crear el bus.");
        redirectSection("buses");
    }

    if ($action === "update_bus") {
        $idBus = (int) ($_POST["id_bus"] ?? 0);
        $patente = normalizePatente($_POST["patente"] ?? "");
        $dueno = trim($_POST["dueno_linea"] ?? "");
        $estado = $_POST["estado"] ?? "activo";

        if (!validPatente($patente) || $dueno === "") {
            flash("error", "Revisa patente y dueño/línea antes de guardar.");
            redirectSection("buses");
        }

        $duplicado = firstValue(
            $conn,
            "SELECT COUNT(*) FROM buses WHERE patente = ? AND id_ruta = ? AND id_bus <> ?",
            [$patente, $idRuta, $idBus]
        );

        if ($duplicado > 0) {
            flash("error", "Otra micro de tu ruta ya usa esa patente.");
            redirectSection("buses");
        }

        $stmt = sqlsrv_query(
            $conn,
            "UPDATE buses SET patente = ?, dueno_linea = ?, estado = ? WHERE id_bus = ? AND id_ruta = ?",
            [$patente, $dueno, $estado, $idBus, $idRuta]
        );

        flash($stmt ? "success" : "error", $stmt ? "Bus actualizado." : "No se pudo actualizar el bus.");
        redirectSection("buses");
    }

    if ($action === "delete_bus") {
        $idBus = (int) ($_POST["id_bus"] ?? 0);
        $relaciones = firstValue(
            $conn,
            "SELECT
                (SELECT COUNT(*) FROM horarios WHERE id_bus = ?) +
                (SELECT COUNT(*) FROM viajes WHERE id_bus = ?) +
                (SELECT COUNT(*) FROM ubicaciones WHERE id_bus = ?)",
            [$idBus, $idBus, $idBus]
        );

        if ($relaciones > 0) {
            $stmt = sqlsrv_query($conn, "UPDATE buses SET estado = 'inactivo' WHERE id_bus = ? AND id_ruta = ?", [$idBus, $idRuta]);
            flash($stmt ? "success" : "error", $stmt ? "El bus tiene historial, así que fue desactivado en vez de eliminado." : "No se pudo desactivar el bus.");
            redirectSection("buses");
        }

        $stmt = sqlsrv_query($conn, "DELETE FROM buses WHERE id_bus = ? AND id_ruta = ?", [$idBus, $idRuta]);
        flash($stmt ? "success" : "error", $stmt ? "Bus eliminado." : "No se pudo eliminar el bus.");
        redirectSection("buses");
    }

    if ($action === "create_conductor") {
        $usuario = trim($_POST["usuario"] ?? "");
        $password = trim($_POST["password"] ?? "");
        $nombre = trim($_POST["nombre"] ?? "");
        $estado = $_POST["estado"] ?? "activo";

        if ($usuario === "" || $password === "") {
            flash("error", "Usuario y contraseña son obligatorios.");
            redirectSection("conductores");
        }

        $existe = firstValue($conn, "SELECT COUNT(*) FROM conductores WHERE usuario = ?", [$usuario]);

        if ($existe > 0) {
            flash("error", "Ya existe un conductor con ese usuario.");
            redirectSection("conductores");
        }

        $stmt = sqlsrv_query(
            $conn,
            "INSERT INTO conductores (usuario, password, nombre, estado, id_ruta, fecha_creacion) VALUES (?, ?, ?, ?, ?, GETDATE())",
            [$usuario, hashUserPassword($password), $nombre, $estado, $idRuta]
        );

        flash($stmt ? "success" : "error", $stmt ? "Conductor creado correctamente." : "No se pudo crear el conductor.");
        redirectSection("conductores");
    }

    if ($action === "update_conductor") {
        $idConductor = (int) ($_POST["id"] ?? 0);
        $usuario = trim($_POST["usuario"] ?? "");
        $password = trim($_POST["password"] ?? "");
        $nombre = trim($_POST["nombre"] ?? "");
        $estado = $_POST["estado"] ?? "activo";

        if ($usuario === "") {
            flash("error", "El usuario del conductor es obligatorio.");
            redirectSection("conductores");
        }

        $duplicado = firstValue($conn, "SELECT COUNT(*) FROM conductores WHERE usuario = ? AND id <> ?", [$usuario, $idConductor]);

        if ($duplicado > 0) {
            flash("error", "Otro conductor ya usa ese usuario.");
            redirectSection("conductores");
        }

        if ($password !== "") {
            $stmt = sqlsrv_query(
                $conn,
                "UPDATE conductores SET usuario = ?, password = ?, nombre = ?, estado = ?, fecha_baja = CASE WHEN ? = 'inactivo' THEN GETDATE() ELSE NULL END WHERE id = ? AND id_ruta = ?",
                [$usuario, hashUserPassword($password), $nombre, $estado, $estado, $idConductor, $idRuta]
            );
        } else {
            $stmt = sqlsrv_query(
                $conn,
                "UPDATE conductores SET usuario = ?, nombre = ?, estado = ?, fecha_baja = CASE WHEN ? = 'inactivo' THEN GETDATE() ELSE NULL END WHERE id = ? AND id_ruta = ?",
                [$usuario, $nombre, $estado, $estado, $idConductor, $idRuta]
            );
        }

        flash($stmt ? "success" : "error", $stmt ? "Conductor actualizado." : "No se pudo actualizar el conductor.");
        redirectSection("conductores");
    }

    if ($action === "delete_conductor") {
        $idConductor = (int) ($_POST["id"] ?? 0);
        $stmt = sqlsrv_query(
            $conn,
            "UPDATE conductores SET estado = 'inactivo', fecha_baja = GETDATE() WHERE id = ? AND id_ruta = ?",
            [$idConductor, $idRuta]
        );

        flash($stmt ? "success" : "error", $stmt ? "Conductor dado de baja." : "No se pudo dar de baja al conductor.");
        redirectSection("conductores");
    }

    if ($action === "create_horario") {
        $idBus = (int) ($_POST["id_bus"] ?? 0);
        $horaSalida = trim($_POST["hora_salida"] ?? "");
        $diaSemana = trim($_POST["dia_semana"] ?? "");
        $direccion = trim($_POST["direccion"] ?? "");

        if ($idBus <= 0 || $horaSalida === "" || $direccion === "") {
            flash("error", "Debes seleccionar bus, hora y dirección.");
            redirectSection("horarios");
        }

        $busActivo = firstValue($conn, "SELECT COUNT(*) FROM buses WHERE id_bus = ? AND id_ruta = ? AND estado = 'activo'", [$idBus, $idRuta]);

        if ((int) $busActivo === 0) {
            flash("error", "Solo puedes crear horarios para buses activos de tu ruta.");
            redirectSection("horarios");
        }

        $stmt = sqlsrv_query(
            $conn,
            "INSERT INTO horarios (id_bus, hora_salida, dia_semana, direccion) VALUES (?, ?, ?, ?)",
            [$idBus, $horaSalida, $diaSemana, $direccion]
        );

        flash($stmt ? "success" : "error", $stmt ? "Horario creado correctamente." : "No se pudo crear el horario.");
        redirectSection("horarios");
    }

    if ($action === "update_horario") {
        $idHorario = (int) ($_POST["id_horario"] ?? 0);
        $idBus = (int) ($_POST["id_bus"] ?? 0);
        $horaSalida = trim($_POST["hora_salida"] ?? "");
        $diaSemana = trim($_POST["dia_semana"] ?? "");
        $direccion = trim($_POST["direccion"] ?? "");

        $busActivo = firstValue($conn, "SELECT COUNT(*) FROM buses WHERE id_bus = ? AND id_ruta = ? AND estado = 'activo'", [$idBus, $idRuta]);

        if ((int) $busActivo === 0 || $horaSalida === "" || $direccion === "") {
            flash("error", "El horario necesita bus activo, hora y dirección.");
            redirectSection("horarios");
        }

        $stmt = sqlsrv_query(
            $conn,
            "UPDATE h
             SET h.id_bus = ?, h.hora_salida = ?, h.dia_semana = ?, h.direccion = ?
             FROM horarios h
             INNER JOIN buses b ON h.id_bus = b.id_bus
             WHERE h.id_horario = ?
             AND b.id_ruta = ?",
            [$idBus, $horaSalida, $diaSemana, $direccion, $idHorario, $idRuta]
        );

        flash($stmt ? "success" : "error", $stmt ? "Horario actualizado." : "No se pudo actualizar el horario.");
        redirectSection("horarios");
    }

    if ($action === "delete_horario") {
        $idHorario = (int) ($_POST["id_horario"] ?? 0);
        $stmt = sqlsrv_query(
            $conn,
            "DELETE FROM horarios
             WHERE id_horario IN (
                SELECT h.id_horario
                FROM horarios h
                INNER JOIN buses b ON h.id_bus = b.id_bus
                WHERE h.id_horario = ?
                AND b.id_ruta = ?
             )",
            [$idHorario, $idRuta]
        );

        flash($stmt ? "success" : "error", $stmt ? "Horario eliminado." : "No se pudo eliminar el horario.");
        redirectSection("horarios");
    }

    if ($action === "create_paradero") {
        $nombre = trim($_POST["nombre"] ?? "");
        $lat = trim($_POST["lat"] ?? "");
        $lng = trim($_POST["lng"] ?? "");
        $estado = $_POST["estado"] ?? "activo";

        if ($nombre === "") {
            flash("error", "El nombre es obligatorio para crear un paradero.");
            redirectSection("paraderos");
        }

        if (!validCoordinate($lat, -90, 90) || !validCoordinate($lng, -180, 180)) {
            flash("error", "La latitud o longitud del paradero no es válida.");
            redirectSection("paraderos");
        }

        $stmt = sqlsrv_query(
            $conn,
            "INSERT INTO paraderos (id_ruta, nombre, lat, lng, estado) VALUES (?, ?, ?, ?, ?)",
            [$idRuta, $nombre, $lat, $lng, $estado]
        );

        flash($stmt ? "success" : "error", $stmt ? "Paradero creado correctamente." : "No se pudo crear el paradero.");
        redirectSection("paraderos");
    }

    if ($action === "update_paradero") {
        $idParadero = (int) ($_POST["id_paradero"] ?? 0);
        $nombre = trim($_POST["nombre"] ?? "");
        $lat = trim($_POST["lat"] ?? "");
        $lng = trim($_POST["lng"] ?? "");
        $estado = $_POST["estado"] ?? "activo";

        if ($nombre === "") {
            flash("error", "Revisa el nombre antes de guardar.");
            redirectSection("paraderos");
        }

        if (!validCoordinate($lat, -90, 90) || !validCoordinate($lng, -180, 180)) {
            flash("error", "La latitud o longitud del paradero no es válida.");
            redirectSection("paraderos");
        }

        $stmt = sqlsrv_query(
            $conn,
            "UPDATE paraderos
             SET nombre = ?, lat = ?, lng = ?, estado = ?
             WHERE id_paradero = ?
             AND id_ruta = ?",
            [$nombre, $lat, $lng, $estado, $idParadero, $idRuta]
        );

        flash($stmt ? "success" : "error", $stmt ? "Paradero actualizado." : "No se pudo actualizar el paradero.");
        redirectSection("paraderos");
    }

    if ($action === "delete_paradero") {
        $idParadero = (int) ($_POST["id_paradero"] ?? 0);
        $stmt = sqlsrv_query(
            $conn,
            "UPDATE paraderos SET estado = 'inactivo' WHERE id_paradero = ? AND id_ruta = ?",
            [$idParadero, $idRuta]
        );

        flash($stmt ? "success" : "error", $stmt ? "Paradero desactivado." : "No se pudo desactivar el paradero.");
        redirectSection("paraderos");
    }
}

$flash = $_SESSION["admin_flash"] ?? null;
unset($_SESSION["admin_flash"]);

$rutaStmt = sqlsrv_query($conn, "SELECT nombre_ruta, inicio, fin FROM rutas WHERE id_ruta = ?", [$idRuta]);
$ruta = $rutaStmt ? (sqlsrv_fetch_array($rutaStmt, SQLSRV_FETCH_ASSOC) ?: []) : [];

$buscarBus = trim($_GET["buscar_bus"] ?? "");
$buscarConductor = trim($_GET["buscar_conductor"] ?? "");
$buscarHorario = trim($_GET["buscar_horario"] ?? "");
$buscarParadero = trim($_GET["buscar_paradero"] ?? "");

$busesParams = [$idRuta];
$busesSql = "SELECT id_bus, patente, dueno_linea, estado FROM buses WHERE id_ruta = ?";

if ($buscarBus !== "") {
    $busesSql .= " AND (patente LIKE ? OR dueno_linea LIKE ? OR estado LIKE ?)";
    $busesParams[] = "%{$buscarBus}%";
    $busesParams[] = "%{$buscarBus}%";
    $busesParams[] = "%{$buscarBus}%";
}

$busesSql .= " ORDER BY patente";
$buses = fetchRows(sqlsrv_query($conn, $busesSql, $busesParams));

$todosBuses = fetchRows(sqlsrv_query($conn, "SELECT id_bus, patente, dueno_linea, estado FROM buses WHERE id_ruta = ? ORDER BY patente", [$idRuta]));
$busesActivos = array_values(array_filter($todosBuses, fn($bus) => ($bus["estado"] ?? "") === "activo"));

$conductoresParams = [$idRuta];
$conductoresSql = "SELECT id, usuario, nombre, estado, fecha_creacion, fecha_baja
                   FROM conductores
                   WHERE id_ruta = ?";

if ($buscarConductor !== "") {
    $conductoresSql .= " AND (usuario LIKE ? OR nombre LIKE ? OR estado LIKE ?)";
    $conductoresParams[] = "%{$buscarConductor}%";
    $conductoresParams[] = "%{$buscarConductor}%";
    $conductoresParams[] = "%{$buscarConductor}%";
}

$conductoresSql .= " ORDER BY estado, nombre, usuario";
$conductores = fetchRows(sqlsrv_query($conn, $conductoresSql, $conductoresParams));
$todosConductores = fetchRows(sqlsrv_query($conn, "SELECT id, usuario, nombre, estado FROM conductores WHERE id_ruta = ? ORDER BY usuario", [$idRuta]));

$horariosParams = [$idRuta];
$horariosSql = "SELECT h.id_horario, h.id_bus, b.patente, h.hora_salida, h.dia_semana, h.direccion
                FROM horarios h
                INNER JOIN buses b ON h.id_bus = b.id_bus
                WHERE b.id_ruta = ?";

if ($buscarHorario !== "") {
    $horariosSql .= " AND (b.patente LIKE ? OR h.direccion LIKE ? OR h.dia_semana LIKE ?)";
    $horariosParams[] = "%{$buscarHorario}%";
    $horariosParams[] = "%{$buscarHorario}%";
    $horariosParams[] = "%{$buscarHorario}%";
}

$horariosSql .= " ORDER BY h.hora_salida, h.direccion";
$horarios = fetchRows(sqlsrv_query($conn, $horariosSql, $horariosParams));

$paraderosParams = [$idRuta];
$paraderosSql = "SELECT id_paradero, nombre, lat, lng, estado
                 FROM paraderos
                 WHERE id_ruta = ?";

if ($buscarParadero !== "") {
    $paraderosSql .= " AND (nombre LIKE ? OR estado LIKE ?)";
    $paraderosParams[] = "%{$buscarParadero}%";
    $paraderosParams[] = "%{$buscarParadero}%";
}

$paraderosSql .= " ORDER BY nombre";
$paraderos = fetchRows(sqlsrv_query($conn, $paraderosSql, $paraderosParams));

$viajeFiltros = [
    "desde" => trim($_GET["desde"] ?? ""),
    "hasta" => trim($_GET["hasta"] ?? ""),
    "estado" => trim($_GET["estado"] ?? ""),
    "id_bus" => (int) ($_GET["id_bus"] ?? 0),
    "id_conductor" => (int) ($_GET["id_conductor"] ?? 0),
    "direccion" => trim($_GET["direccion"] ?? "")
];

$viajesParams = [$idRuta];
$viajesWhere = "WHERE v.id_ruta = ?";

if ($viajeFiltros["desde"] !== "") {
    $viajesWhere .= " AND CONVERT(date, v.hora_inicio) >= ?";
    $viajesParams[] = $viajeFiltros["desde"];
}

if ($viajeFiltros["hasta"] !== "") {
    $viajesWhere .= " AND CONVERT(date, v.hora_inicio) <= ?";
    $viajesParams[] = $viajeFiltros["hasta"];
}

if ($viajeFiltros["estado"] !== "") {
    $viajesWhere .= " AND v.estado = ?";
    $viajesParams[] = $viajeFiltros["estado"];
}

if ($viajeFiltros["id_bus"] > 0) {
    $viajesWhere .= " AND v.id_bus = ?";
    $viajesParams[] = $viajeFiltros["id_bus"];
}

if ($viajeFiltros["id_conductor"] > 0) {
    $viajesWhere .= " AND v.id_conductor = ?";
    $viajesParams[] = $viajeFiltros["id_conductor"];
}

if ($viajeFiltros["direccion"] !== "") {
    $viajesWhere .= " AND v.direccion LIKE ?";
    $viajesParams[] = "%{$viajeFiltros["direccion"]}%";
}

$viajes = fetchRows(sqlsrv_query(
    $conn,
    "SELECT TOP 120
        v.id_viaje,
        v.estado,
        v.direccion,
        v.hora_inicio,
        v.hora_fin,
        v.kilometros_recorridos,
        v.observacion,
        b.patente,
        c.usuario AS conductor
     FROM viajes v
     INNER JOIN buses b ON v.id_bus = b.id_bus
     LEFT JOIN conductores c ON v.id_conductor = c.id
     {$viajesWhere}
     ORDER BY v.hora_inicio DESC",
    $viajesParams
));

$onlineBuses = fetchRows(sqlsrv_query(
    $conn,
    "SELECT
        b.id_bus,
        b.patente,
        b.dueno_linea,
        u.lat,
        u.lng,
        u.fecha,
        u.velocidad,
        v.id_viaje,
        v.direccion,
        c.usuario AS conductor,
        DATEDIFF(MINUTE, u.fecha, GETDATE()) AS minutos
     FROM buses b
     INNER JOIN (
        SELECT id_bus, MAX(fecha) AS ultima_fecha
        FROM ubicaciones
        GROUP BY id_bus
     ) ult ON b.id_bus = ult.id_bus
     INNER JOIN ubicaciones u ON u.id_bus = ult.id_bus AND u.fecha = ult.ultima_fecha
     LEFT JOIN viajes v ON u.id_viaje = v.id_viaje
     LEFT JOIN conductores c ON v.id_conductor = c.id
     WHERE b.id_ruta = ?
     AND DATEDIFF(MINUTE, u.fecha, GETDATE()) <= 5
     ORDER BY u.fecha DESC",
    [$idRuta]
));

$stats = [
    "buses" => firstValue($conn, "SELECT COUNT(*) FROM buses WHERE id_ruta = ?", [$idRuta]),
    "buses_inactivos" => firstValue($conn, "SELECT COUNT(*) FROM buses WHERE id_ruta = ? AND estado = 'inactivo'", [$idRuta]),
    "conductores" => firstValue($conn, "SELECT COUNT(*) FROM conductores WHERE id_ruta = ? AND estado = 'activo'", [$idRuta]),
    "conductores_inactivos" => firstValue($conn, "SELECT COUNT(*) FROM conductores WHERE id_ruta = ? AND estado = 'inactivo'", [$idRuta]),
    "horarios" => firstValue($conn, "SELECT COUNT(*) FROM horarios h INNER JOIN buses b ON h.id_bus = b.id_bus WHERE b.id_ruta = ?", [$idRuta]),
    "paraderos" => firstValue($conn, "SELECT COUNT(*) FROM paraderos WHERE id_ruta = ? AND estado = 'activo'", [$idRuta]),
    "viajes_activos" => firstValue($conn, "SELECT COUNT(*) FROM viajes WHERE id_ruta = ? AND estado = 'activo'", [$idRuta]),
    "viajes_finalizados" => firstValue($conn, "SELECT COUNT(*) FROM viajes WHERE id_ruta = ? AND estado = 'finalizado'", [$idRuta]),
    "kilometros" => firstValue($conn, "SELECT ISNULL(SUM(kilometros_recorridos), 0) FROM viajes WHERE id_ruta = ? AND estado = 'finalizado'", [$idRuta]),
    "buses_gps" => count($onlineBuses)
];

$chartViajesDia = fetchRows(sqlsrv_query(
    $conn,
    "SELECT TOP 7 CONVERT(date, hora_inicio) AS dia, COUNT(*) AS total
     FROM viajes
     WHERE id_ruta = ?
     GROUP BY CONVERT(date, hora_inicio)
     ORDER BY dia DESC",
    [$idRuta]
));
$chartViajesDia = array_reverse($chartViajesDia);
$maxViajesDia = max(1, ...array_map(fn($row) => (int) $row["total"], $chartViajesDia ?: [["total" => 1]]));

$chartKmDia = fetchRows(sqlsrv_query(
    $conn,
    "SELECT TOP 7 CONVERT(date, hora_inicio) AS dia, ISNULL(SUM(kilometros_recorridos), 0) AS total
     FROM viajes
     WHERE id_ruta = ?
     AND estado = 'finalizado'
     GROUP BY CONVERT(date, hora_inicio)
     ORDER BY dia DESC",
    [$idRuta]
));
$chartKmDia = array_reverse($chartKmDia);
$maxKmDia = max(1, ...array_map(fn($row) => (float) $row["total"], $chartKmDia ?: [["total" => 1]]));

$detalleViaje = null;
$detallePuntos = [];

if ($section === "detalle_viaje") {
    $idDetalle = (int) ($_GET["id"] ?? 0);
    $detalleStmt = sqlsrv_query(
        $conn,
        "SELECT
            v.*,
            b.patente,
            b.dueno_linea,
            c.usuario AS conductor_usuario,
            c.nombre AS conductor_nombre
         FROM viajes v
         INNER JOIN buses b ON v.id_bus = b.id_bus
         LEFT JOIN conductores c ON v.id_conductor = c.id
         WHERE v.id_viaje = ?
         AND v.id_ruta = ?",
        [$idDetalle, $idRuta]
    );
    $detalleViaje = $detalleStmt ? sqlsrv_fetch_array($detalleStmt, SQLSRV_FETCH_ASSOC) : null;

    if ($detalleViaje) {
        $detallePuntos = fetchRows(sqlsrv_query(
            $conn,
            "SELECT TOP 20 lat, lng, fecha, velocidad, estado
             FROM ubicaciones
             WHERE id_viaje = ?
             ORDER BY fecha DESC",
            [$idDetalle]
        ));
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Panel Admin - Viene La Micro</title>
<link rel="icon" type="image/png" href="../busicono.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<link rel="stylesheet" href="../assets/css/admin.css">
</head>

<body>
<aside class="admin-sidebar">
    <a class="admin-brand" href="dashboard.php">
        <span><i class="fa-solid fa-bus-simple"></i></span>
        <strong>Viene la Micro</strong>
    </a>

    <nav>
        <a class="<?= $section === "resumen" ? "active" : "" ?>" href="dashboard.php?section=resumen"><i class="fa-solid fa-chart-line"></i> Dashboard</a>
        <a class="<?= $section === "buses" ? "active" : "" ?>" href="dashboard.php?section=buses"><i class="fa-solid fa-bus"></i> Buses</a>
        <a class="<?= $section === "conductores" ? "active" : "" ?>" href="dashboard.php?section=conductores"><i class="fa-solid fa-id-card"></i> Conductores</a>
        <a class="<?= $section === "horarios" ? "active" : "" ?>" href="dashboard.php?section=horarios"><i class="fa-solid fa-clock"></i> Horarios</a>
        <a class="<?= $section === "paraderos" ? "active" : "" ?>" href="dashboard.php?section=paraderos"><i class="fa-solid fa-location-dot"></i> Paraderos</a>
        <a class="<?= $section === "viajes" || $section === "detalle_viaje" ? "active" : "" ?>" href="dashboard.php?section=viajes"><i class="fa-solid fa-route"></i> Viajes</a>
        <a class="<?= $section === "online" ? "active" : "" ?>" href="dashboard.php?section=online"><i class="fa-solid fa-satellite-dish"></i> Online</a>
    </nav>

    <div class="sidebar-footer">
        <a href="../index.php"><i class="fa-solid fa-house"></i> Ver sitio</a>
        <a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Cerrar sesión</a>
    </div>
</aside>

<main class="admin-main">
    <header class="admin-topbar">
        <div>
            <span class="eyebrow">Panel administrador</span>
            <h1><?= h($ruta["nombre_ruta"] ?? "Ruta asignada") ?></h1>
            <p><?= h($ruta["inicio"] ?? "") ?> <?= !empty($ruta) ? "→" : "" ?> <?= h($ruta["fin"] ?? "") ?></p>
        </div>

        <div class="admin-user">
            <span><?= h($adminUsuario) ?></span>
            <small>Ruta #<?= h($idRuta) ?></small>
        </div>
    </header>

    <?php if ($flash): ?>
        <div class="flash <?= h($flash["type"]) ?>">
            <?= h($flash["message"]) ?>
        </div>
    <?php endif; ?>

    <?php if ($section === "resumen"): ?>
        <section class="stats-grid">
            <article><span>Buses</span><strong><?= h($stats["buses"]) ?></strong></article>
            <article><span>Conductores activos</span><strong><?= h($stats["conductores"]) ?></strong></article>
            <article><span>Horarios</span><strong><?= h($stats["horarios"]) ?></strong></article>
            <article><span>Paraderos activos</span><strong><?= h($stats["paraderos"]) ?></strong></article>
            <article><a href="dashboard.php?section=online"><span>GPS últimos 5 min</span><strong><?= h($stats["buses_gps"]) ?></strong></a></article>
            <article><span>Viajes activos</span><strong><?= h($stats["viajes_activos"]) ?></strong></article>
            <article><span>Viajes finalizados</span><strong><?= h($stats["viajes_finalizados"]) ?></strong></article>
            <article class="wide"><span>Kilómetros registrados</span><strong><?= number_format((float) $stats["kilometros"], 2, ",", ".") ?> km</strong></article>
        </section>

        <section class="dashboard-grid">
            <article class="admin-panel">
                <div class="panel-heading">
                    <div>
                        <span>Gráfico</span>
                        <h2>Viajes por día</h2>
                    </div>
                </div>

                <div class="chart-list">
                    <?php foreach ($chartViajesDia as $row): ?>
                        <div class="chart-row">
                            <span><?= h(formatDateValue($row["dia"])) ?></span>
                            <progress max="<?= h($maxViajesDia) ?>" value="<?= h((int) $row["total"]) ?>"></progress>
                            <strong><?= h((int) $row["total"]) ?></strong>
                        </div>
                    <?php endforeach; ?>
                    <?php if (!$chartViajesDia): ?><p class="empty">Sin datos de viajes todavía.</p><?php endif; ?>
                </div>
            </article>

            <article class="admin-panel">
                <div class="panel-heading">
                    <div>
                        <span>Gráfico</span>
                        <h2>Kilómetros por día</h2>
                    </div>
                </div>

                <div class="chart-list">
                    <?php foreach ($chartKmDia as $row): ?>
                        <div class="chart-row">
                            <span><?= h(formatDateValue($row["dia"])) ?></span>
                            <progress max="<?= h($maxKmDia) ?>" value="<?= h((float) $row["total"]) ?>"></progress>
                            <strong><?= number_format((float) $row["total"], 1, ",", ".") ?></strong>
                        </div>
                    <?php endforeach; ?>
                    <?php if (!$chartKmDia): ?><p class="empty">Sin kilómetros registrados todavía.</p><?php endif; ?>
                </div>
            </article>
        </section>

        <section class="admin-panel">
            <div class="panel-heading">
                <div>
                    <span>Actividad</span>
                    <h2>Últimos viajes</h2>
                </div>
                <a class="small-link" href="dashboard.php?section=viajes">Ver todos</a>
            </div>

            <div class="admin-list">
                <?php foreach (array_slice($viajes, 0, 8) as $viaje): ?>
                    <div class="admin-row read-row">
                        <div>
                            <small>#<?= h($viaje["id_viaje"]) ?> · <?= h($viaje["estado"]) ?></small>
                            <strong><?= h($viaje["patente"]) ?> · <?= h($viaje["direccion"]) ?></strong>
                        </div>
                        <div><?= h($viaje["conductor"] ?? "Sin conductor") ?></div>
                        <div><?= h(formatDateTimeValue($viaje["hora_inicio"])) ?></div>
                        <a class="row-action" href="dashboard.php?section=detalle_viaje&id=<?= h($viaje["id_viaje"]) ?>">Detalle</a>
                    </div>
                <?php endforeach; ?>

                <?php if (!$viajes): ?>
                    <p class="empty">Aún no hay viajes registrados para esta ruta.</p>
                <?php endif; ?>
            </div>
        </section>
    <?php endif; ?>

    <?php if ($section === "buses"): ?>
        <section class="admin-panel">
            <div class="panel-heading">
                <div>
                    <span>CRUD</span>
                    <h2>Buses de la ruta</h2>
                </div>
            </div>

            <form class="search-form" method="GET">
                <input type="hidden" name="section" value="buses">
                <input name="buscar_bus" value="<?= h($buscarBus) ?>" placeholder="Buscar por patente, línea o estado">
                <button type="submit"><i class="fa-solid fa-magnifying-glass"></i> Buscar</button>
                <a class="row-action" href="dashboard.php?section=buses">Limpiar</a>
            </form>

            <form class="create-form" method="POST">
                <input type="hidden" name="action" value="create_bus">
                <input name="patente" placeholder="Patente" required>
                <input name="dueno_linea" placeholder="Dueño / línea" required>
                <select name="estado">
                    <option value="activo">Activo</option>
                    <option value="inactivo">Inactivo</option>
                </select>
                <button type="submit"><i class="fa-solid fa-plus"></i> Crear bus</button>
            </form>

            <div class="admin-list">
                <?php foreach ($buses as $bus): ?>
                    <form class="admin-row editable-row" method="POST">
                        <input type="hidden" name="action" value="update_bus">
                        <input type="hidden" name="id_bus" value="<?= h($bus["id_bus"]) ?>">
                        <input name="patente" value="<?= h($bus["patente"]) ?>" required>
                        <input name="dueno_linea" value="<?= h($bus["dueno_linea"]) ?>" required>
                        <select name="estado">
                            <option value="activo" <?= ($bus["estado"] ?? "") === "activo" ? "selected" : "" ?>>Activo</option>
                            <option value="inactivo" <?= ($bus["estado"] ?? "") === "inactivo" ? "selected" : "" ?>>Inactivo</option>
                        </select>
                        <button type="submit">Guardar</button>
                        <button class="danger" type="submit" name="action" value="delete_bus" onclick="return confirm('¿Eliminar este bus? Si tiene historial quedará inactivo.');">Eliminar</button>
                    </form>
                <?php endforeach; ?>
                <?php if (!$buses): ?><p class="empty">No hay buses con ese filtro.</p><?php endif; ?>
            </div>
        </section>
    <?php endif; ?>

    <?php if ($section === "conductores"): ?>
        <section class="admin-panel">
            <div class="panel-heading">
                <div>
                    <span>CRUD</span>
                    <h2>Conductores</h2>
                </div>
            </div>

            <form class="search-form" method="GET">
                <input type="hidden" name="section" value="conductores">
                <input name="buscar_conductor" value="<?= h($buscarConductor) ?>" placeholder="Buscar por nombre, usuario o estado">
                <button type="submit"><i class="fa-solid fa-magnifying-glass"></i> Buscar</button>
                <a class="row-action" href="dashboard.php?section=conductores">Limpiar</a>
            </form>

            <form class="create-form" method="POST">
                <input type="hidden" name="action" value="create_conductor">
                <input name="nombre" placeholder="Nombre">
                <input name="usuario" placeholder="Usuario" required>
                <input name="password" placeholder="Contraseña" required>
                <select name="estado">
                    <option value="activo">Activo</option>
                    <option value="inactivo">Inactivo</option>
                </select>
                <button type="submit"><i class="fa-solid fa-plus"></i> Crear conductor</button>
            </form>

            <div class="admin-list">
                <?php foreach ($conductores as $conductor): ?>
                    <form class="admin-row editable-row conductor-row" method="POST">
                        <input type="hidden" name="action" value="update_conductor">
                        <input type="hidden" name="id" value="<?= h($conductor["id"]) ?>">
                        <input name="nombre" value="<?= h($conductor["nombre"] ?? "") ?>" placeholder="Nombre">
                        <input name="usuario" value="<?= h($conductor["usuario"]) ?>" required>
                        <input name="password" placeholder="Nueva contraseña">
                        <select name="estado">
                            <option value="activo" <?= ($conductor["estado"] ?? "") === "activo" ? "selected" : "" ?>>Activo</option>
                            <option value="inactivo" <?= ($conductor["estado"] ?? "") === "inactivo" ? "selected" : "" ?>>Inactivo</option>
                        </select>
                        <button type="submit">Guardar</button>
                        <button class="danger" type="submit" name="action" value="delete_conductor" onclick="return confirm('¿Dar de baja este conductor?');">Dar de baja</button>
                    </form>
                <?php endforeach; ?>
                <?php if (!$conductores): ?><p class="empty">No hay conductores con ese filtro.</p><?php endif; ?>
            </div>
        </section>
    <?php endif; ?>

    <?php if ($section === "horarios"): ?>
        <section class="admin-panel">
            <div class="panel-heading">
                <div>
                    <span>CRUD</span>
                    <h2>Horarios programados</h2>
                </div>
            </div>

            <form class="search-form" method="GET">
                <input type="hidden" name="section" value="horarios">
                <input name="buscar_horario" value="<?= h($buscarHorario) ?>" placeholder="Buscar por patente, día o dirección">
                <button type="submit"><i class="fa-solid fa-magnifying-glass"></i> Buscar</button>
                <a class="row-action" href="dashboard.php?section=horarios">Limpiar</a>
            </form>

            <form class="create-form horario-form" method="POST">
                <input type="hidden" name="action" value="create_horario">
                <select name="id_bus" required>
                    <option value="">Bus activo</option>
                    <?php foreach ($busesActivos as $bus): ?>
                        <option value="<?= h($bus["id_bus"]) ?>"><?= h($bus["patente"]) ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="time" name="hora_salida" required>
                <input name="dia_semana" placeholder="Día semana">
                <input name="direccion" placeholder="Dirección del recorrido" required>
                <button type="submit"><i class="fa-solid fa-plus"></i> Crear horario</button>
            </form>

            <div class="admin-list">
                <?php foreach ($horarios as $horario): ?>
                    <form class="admin-row editable-row horario-row" method="POST">
                        <input type="hidden" name="action" value="update_horario">
                        <input type="hidden" name="id_horario" value="<?= h($horario["id_horario"]) ?>">
                        <select name="id_bus" required>
                            <?php foreach ($busesActivos as $bus): ?>
                                <option value="<?= h($bus["id_bus"]) ?>" <?= (int) $bus["id_bus"] === (int) $horario["id_bus"] ? "selected" : "" ?>>
                                    <?= h($bus["patente"]) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <input type="time" name="hora_salida" value="<?= h(formatTimeValue($horario["hora_salida"])) ?>" required>
                        <input name="dia_semana" value="<?= h($horario["dia_semana"] ?? "") ?>" placeholder="Día">
                        <input name="direccion" value="<?= h($horario["direccion"]) ?>" required>
                        <button type="submit">Guardar</button>
                        <button class="danger" type="submit" name="action" value="delete_horario" onclick="return confirm('¿Eliminar este horario?');">Eliminar</button>
                    </form>
                <?php endforeach; ?>
                <?php if (!$horarios): ?><p class="empty">No hay horarios con ese filtro.</p><?php endif; ?>
            </div>
        </section>
    <?php endif; ?>

    <?php if ($section === "paraderos"): ?>
        <section class="admin-panel">
            <div class="panel-heading">
                <div>
                    <span>CRUD</span>
                    <h2>Paraderos de la ruta</h2>
                </div>
                <a class="small-link" href="../mapa.html">Ver mapa</a>
            </div>

            <form class="search-form" method="GET">
                <input type="hidden" name="section" value="paraderos">
                <input name="buscar_paradero" value="<?= h($buscarParadero) ?>" placeholder="Buscar por nombre o estado">
                <button type="submit"><i class="fa-solid fa-magnifying-glass"></i> Buscar</button>
                <a class="row-action" href="dashboard.php?section=paraderos">Limpiar</a>
            </form>

            <form class="create-form paradero-form" method="POST">
                <input type="hidden" name="action" value="create_paradero">
                <input name="nombre" placeholder="Nombre del paradero" required>
                <input name="lat" placeholder="Latitud" required>
                <input name="lng" placeholder="Longitud" required>
                <select name="estado">
                    <option value="activo">Activo</option>
                    <option value="inactivo">Inactivo</option>
                </select>
                <button type="submit"><i class="fa-solid fa-plus"></i> Crear paradero</button>
            </form>

            <div class="admin-list">
                <?php foreach ($paraderos as $paradero): ?>
                    <form class="admin-row editable-row paradero-row" method="POST">
                        <input type="hidden" name="action" value="update_paradero">
                        <input type="hidden" name="id_paradero" value="<?= h($paradero["id_paradero"]) ?>">
                        <input name="nombre" value="<?= h($paradero["nombre"]) ?>" required>
                        <input name="lat" value="<?= h($paradero["lat"]) ?>" required>
                        <input name="lng" value="<?= h($paradero["lng"]) ?>" required>
                        <select name="estado">
                            <option value="activo" <?= ($paradero["estado"] ?? "") === "activo" ? "selected" : "" ?>>Activo</option>
                            <option value="inactivo" <?= ($paradero["estado"] ?? "") === "inactivo" ? "selected" : "" ?>>Inactivo</option>
                        </select>
                        <button type="submit">Guardar</button>
                        <button class="danger" type="submit" name="action" value="delete_paradero" onclick="return confirm('¿Desactivar este paradero?');">Desactivar</button>
                    </form>
                <?php endforeach; ?>
                <?php if (!$paraderos): ?><p class="empty">No hay paraderos con ese filtro.</p><?php endif; ?>
            </div>
        </section>
    <?php endif; ?>

    <?php if ($section === "online"): ?>
        <section class="admin-panel">
            <div class="panel-heading">
                <div>
                    <span>GPS</span>
                    <h2>Buses online</h2>
                </div>
                <a class="small-link" href="../mapa.html">Abrir mapa</a>
            </div>

            <div class="admin-list">
                <?php foreach ($onlineBuses as $bus): ?>
                    <div class="admin-row online-row">
                        <div>
                            <small><?= h($bus["dueno_linea"]) ?></small>
                            <strong><?= h($bus["patente"]) ?></strong>
                        </div>
                        <div><?= h($bus["direccion"] ?? "Sin viaje activo") ?></div>
                        <div><?= h($bus["conductor"] ?? "Sin conductor") ?></div>
                        <div><?= h(formatDateTimeValue($bus["fecha"])) ?></div>
                        <div><?= number_format((float) ($bus["velocidad"] ?? 0), 1, ",", ".") ?> km/h</div>
                        <?php if (!empty($bus["id_viaje"])): ?>
                            <a class="row-action" href="dashboard.php?section=detalle_viaje&id=<?= h($bus["id_viaje"]) ?>">Detalle</a>
                        <?php else: ?>
                            <span>-</span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
                <?php if (!$onlineBuses): ?><p class="empty">No hay buses reportando GPS en los últimos 5 minutos.</p><?php endif; ?>
            </div>
        </section>
    <?php endif; ?>

    <?php if ($section === "viajes"): ?>
        <section class="admin-panel">
            <div class="panel-heading">
                <div>
                    <span>Filtros</span>
                    <h2>Viajes de la ruta</h2>
                </div>
            </div>

            <form class="filters-form" method="GET">
                <input type="hidden" name="section" value="viajes">
                <label>Desde<input type="date" name="desde" value="<?= h($viajeFiltros["desde"]) ?>"></label>
                <label>Hasta<input type="date" name="hasta" value="<?= h($viajeFiltros["hasta"]) ?>"></label>
                <label>Estado
                    <select name="estado">
                        <option value="">Todos</option>
                        <option value="activo" <?= $viajeFiltros["estado"] === "activo" ? "selected" : "" ?>>Activo</option>
                        <option value="finalizado" <?= $viajeFiltros["estado"] === "finalizado" ? "selected" : "" ?>>Finalizado</option>
                    </select>
                </label>
                <label>Bus
                    <select name="id_bus">
                        <option value="0">Todos</option>
                        <?php foreach ($todosBuses as $bus): ?>
                            <option value="<?= h($bus["id_bus"]) ?>" <?= $viajeFiltros["id_bus"] === (int) $bus["id_bus"] ? "selected" : "" ?>>
                                <?= h($bus["patente"]) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>Conductor
                    <select name="id_conductor">
                        <option value="0">Todos</option>
                        <?php foreach ($todosConductores as $conductor): ?>
                            <option value="<?= h($conductor["id"]) ?>" <?= $viajeFiltros["id_conductor"] === (int) $conductor["id"] ? "selected" : "" ?>>
                                <?= h($conductor["usuario"]) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>Dirección<input name="direccion" value="<?= h($viajeFiltros["direccion"]) ?>" placeholder="Ej: Chillán"></label>
                <button type="submit">Filtrar</button>
                <a class="row-action" href="dashboard.php?section=viajes">Limpiar</a>
            </form>

            <div class="admin-list">
                <?php foreach ($viajes as $viaje): ?>
                    <div class="admin-row read-row viajes-row">
                        <div>
                            <small>#<?= h($viaje["id_viaje"]) ?> · <?= h($viaje["estado"]) ?></small>
                            <strong><?= h($viaje["patente"]) ?></strong>
                        </div>
                        <div><?= h($viaje["conductor"] ?? "Sin conductor") ?></div>
                        <div><?= h($viaje["direccion"]) ?></div>
                        <div><?= h(formatDateTimeValue($viaje["hora_inicio"])) ?></div>
                        <div><?= h(formatDateTimeValue($viaje["hora_fin"])) ?></div>
                        <div><?= number_format((float) ($viaje["kilometros_recorridos"] ?? 0), 2, ",", ".") ?> km</div>
                        <a class="row-action" href="dashboard.php?section=detalle_viaje&id=<?= h($viaje["id_viaje"]) ?>">Detalle</a>
                    </div>
                <?php endforeach; ?>

                <?php if (!$viajes): ?>
                    <p class="empty">No hay viajes con esos filtros.</p>
                <?php endif; ?>
            </div>
        </section>
    <?php endif; ?>

    <?php if ($section === "detalle_viaje"): ?>
        <section class="admin-panel">
            <div class="panel-heading">
                <div>
                    <span>Detalle</span>
                    <h2>Viaje <?= $detalleViaje ? "#" . h($detalleViaje["id_viaje"]) : "" ?></h2>
                </div>
                <a class="small-link" href="dashboard.php?section=viajes">Volver a viajes</a>
            </div>

            <?php if ($detalleViaje): ?>
                <div class="detail-grid">
                    <article><span>Estado</span><strong><?= h($detalleViaje["estado"]) ?></strong></article>
                    <article><span>Bus</span><strong><?= h($detalleViaje["patente"]) ?></strong></article>
                    <article><span>Conductor</span><strong><?= h($detalleViaje["conductor_nombre"] ?: $detalleViaje["conductor_usuario"] ?: "Sin conductor") ?></strong></article>
                    <article><span>Dirección</span><strong><?= h($detalleViaje["direccion"]) ?></strong></article>
                    <article><span>Inicio</span><strong><?= h(formatDateTimeValue($detalleViaje["hora_inicio"])) ?></strong></article>
                    <article><span>Fin</span><strong><?= h(formatDateTimeValue($detalleViaje["hora_fin"])) ?></strong></article>
                    <article><span>Kilómetros</span><strong><?= number_format((float) ($detalleViaje["kilometros_recorridos"] ?? 0), 2, ",", ".") ?> km</strong></article>
                    <article><span>Puntos GPS</span><strong><?= h(count($detallePuntos)) ?> mostrados</strong></article>
                </div>

                <div class="observation-box">
                    <span>Observación del conductor</span>
                    <p><?= h($detalleViaje["observacion"] ?? "Sin observaciones") ?></p>
                </div>

                <div class="admin-list">
                    <?php foreach ($detallePuntos as $punto): ?>
                        <div class="admin-row gps-row">
                            <div><small>Fecha</small><strong><?= h(formatDateTimeValue($punto["fecha"])) ?></strong></div>
                            <div><small>Latitud</small><strong><?= h($punto["lat"]) ?></strong></div>
                            <div><small>Longitud</small><strong><?= h($punto["lng"]) ?></strong></div>
                            <div><small>Velocidad</small><strong><?= number_format((float) ($punto["velocidad"] ?? 0), 1, ",", ".") ?> km/h</strong></div>
                        </div>
                    <?php endforeach; ?>
                    <?php if (!$detallePuntos): ?><p class="empty">Este viaje no tiene puntos GPS registrados.</p><?php endif; ?>
                </div>
            <?php else: ?>
                <p class="empty">No se encontró este viaje para tu ruta.</p>
            <?php endif; ?>
        </section>
    <?php endif; ?>
</main>
</body>
</html>

