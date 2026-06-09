<?php
include("auth.php");
include("../conexion.php");

$idRuta = (int) $_SESSION["id_ruta"];
$adminUsuario = $_SESSION["admin"] ?? "Administrador";
$section = $_GET["section"] ?? "resumen";
$validSections = ["resumen", "buses", "conductores", "horarios", "viajes"];

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

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";

    if ($action === "create_bus") {
        $patente = trim($_POST["patente"] ?? "");
        $dueno = trim($_POST["dueno_linea"] ?? "");
        $estado = $_POST["estado"] ?? "activo";

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
        $patente = trim($_POST["patente"] ?? "");
        $dueno = trim($_POST["dueno_linea"] ?? "");
        $estado = $_POST["estado"] ?? "activo";

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
        $stmt = sqlsrv_query($conn, "DELETE FROM buses WHERE id_bus = ? AND id_ruta = ?", [$idBus, $idRuta]);

        flash($stmt ? "success" : "error", $stmt ? "Bus eliminado." : "No se pudo eliminar el bus. Puede tener horarios, viajes o ubicaciones asociadas.");
        redirectSection("buses");
    }

    if ($action === "create_conductor") {
        $usuario = trim($_POST["usuario"] ?? "");
        $password = trim($_POST["password"] ?? "");
        $nombre = trim($_POST["nombre"] ?? "");
        $estado = $_POST["estado"] ?? "activo";

        $stmt = sqlsrv_query(
            $conn,
            "INSERT INTO conductores (usuario, password, nombre, estado, id_ruta, fecha_creacion) VALUES (?, ?, ?, ?, ?, GETDATE())",
            [$usuario, $password, $nombre, $estado, $idRuta]
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

        if ($password !== "") {
            $stmt = sqlsrv_query(
                $conn,
                "UPDATE conductores SET usuario = ?, password = ?, nombre = ?, estado = ?, fecha_baja = CASE WHEN ? = 'inactivo' THEN GETDATE() ELSE NULL END WHERE id = ? AND id_ruta = ?",
                [$usuario, $password, $nombre, $estado, $estado, $idConductor, $idRuta]
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

        $stmt = sqlsrv_query(
            $conn,
            "INSERT INTO horarios (id_bus, hora_salida, dia_semana, direccion)
             SELECT ?, ?, ?, ?
             WHERE EXISTS (SELECT 1 FROM buses WHERE id_bus = ? AND id_ruta = ?)",
            [$idBus, $horaSalida, $diaSemana, $direccion, $idBus, $idRuta]
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

        $stmt = sqlsrv_query(
            $conn,
            "UPDATE h
             SET h.id_bus = ?, h.hora_salida = ?, h.dia_semana = ?, h.direccion = ?
             FROM horarios h
             INNER JOIN buses b ON h.id_bus = b.id_bus
             WHERE h.id_horario = ?
             AND b.id_ruta = ?
             AND EXISTS (SELECT 1 FROM buses b2 WHERE b2.id_bus = ? AND b2.id_ruta = ?)",
            [$idBus, $horaSalida, $diaSemana, $direccion, $idHorario, $idRuta, $idBus, $idRuta]
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
}

$flash = $_SESSION["admin_flash"] ?? null;
unset($_SESSION["admin_flash"]);

$rutaStmt = sqlsrv_query($conn, "SELECT nombre_ruta, inicio, fin FROM rutas WHERE id_ruta = ?", [$idRuta]);
$ruta = $rutaStmt ? (sqlsrv_fetch_array($rutaStmt, SQLSRV_FETCH_ASSOC) ?: []) : [];

$buses = fetchRows(sqlsrv_query($conn, "SELECT id_bus, patente, dueno_linea, estado FROM buses WHERE id_ruta = ? ORDER BY patente", [$idRuta]));

$conductores = fetchRows(sqlsrv_query(
    $conn,
    "SELECT id, usuario, nombre, estado, fecha_creacion, fecha_baja
     FROM conductores
     WHERE id_ruta = ?
     ORDER BY estado, nombre, usuario",
    [$idRuta]
));

$horarios = fetchRows(sqlsrv_query(
    $conn,
    "SELECT h.id_horario, h.id_bus, b.patente, h.hora_salida, h.dia_semana, h.direccion
     FROM horarios h
     INNER JOIN buses b ON h.id_bus = b.id_bus
     WHERE b.id_ruta = ?
     ORDER BY h.hora_salida, h.direccion",
    [$idRuta]
));

$viajes = fetchRows(sqlsrv_query(
    $conn,
    "SELECT TOP 80
        v.id_viaje,
        v.estado,
        v.direccion,
        v.hora_inicio,
        v.hora_fin,
        v.kilometros_recorridos,
        b.patente,
        c.usuario AS conductor
     FROM viajes v
     INNER JOIN buses b ON v.id_bus = b.id_bus
     LEFT JOIN conductores c ON v.id_conductor = c.id
     WHERE v.id_ruta = ?
     ORDER BY v.hora_inicio DESC",
    [$idRuta]
));

$stats = [
    "buses" => firstValue($conn, "SELECT COUNT(*) FROM buses WHERE id_ruta = ?", [$idRuta]),
    "conductores" => firstValue($conn, "SELECT COUNT(*) FROM conductores WHERE id_ruta = ? AND estado = 'activo'", [$idRuta]),
    "horarios" => firstValue($conn, "SELECT COUNT(*) FROM horarios h INNER JOIN buses b ON h.id_bus = b.id_bus WHERE b.id_ruta = ?", [$idRuta]),
    "viajes_activos" => firstValue($conn, "SELECT COUNT(*) FROM viajes WHERE id_ruta = ? AND estado = 'activo'", [$idRuta]),
    "viajes_finalizados" => firstValue($conn, "SELECT COUNT(*) FROM viajes WHERE id_ruta = ? AND estado = 'finalizado'", [$idRuta]),
    "kilometros" => firstValue($conn, "SELECT ISNULL(SUM(kilometros_recorridos), 0) FROM viajes WHERE id_ruta = ? AND estado = 'finalizado'", [$idRuta]),
    "buses_gps" => firstValue(
        $conn,
        "SELECT COUNT(DISTINCT b.id_bus)
         FROM buses b
         INNER JOIN ubicaciones u ON b.id_bus = u.id_bus
         WHERE b.id_ruta = ?
         AND DATEDIFF(MINUTE, u.fecha, GETDATE()) <= 5",
        [$idRuta]
    )
];
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
        <a class="<?= $section === "viajes" ? "active" : "" ?>" href="dashboard.php?section=viajes"><i class="fa-solid fa-route"></i> Viajes</a>
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
            <article><span>GPS últimos 5 min</span><strong><?= h($stats["buses_gps"]) ?></strong></article>
            <article><span>Viajes activos</span><strong><?= h($stats["viajes_activos"]) ?></strong></article>
            <article><span>Viajes finalizados</span><strong><?= h($stats["viajes_finalizados"]) ?></strong></article>
            <article class="wide"><span>Kilómetros registrados</span><strong><?= number_format((float) $stats["kilometros"], 2, ",", ".") ?> km</strong></article>
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
                        <div><?= number_format((float) ($viaje["kilometros_recorridos"] ?? 0), 2, ",", ".") ?> km</div>
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
                        <button class="danger" type="submit" name="action" value="delete_bus" onclick="return confirm('¿Eliminar este bus?');">Eliminar</button>
                    </form>
                <?php endforeach; ?>
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

            <form class="create-form horario-form" method="POST">
                <input type="hidden" name="action" value="create_horario">
                <select name="id_bus" required>
                    <option value="">Bus</option>
                    <?php foreach ($buses as $bus): ?>
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
                            <?php foreach ($buses as $bus): ?>
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
            </div>
        </section>
    <?php endif; ?>

    <?php if ($section === "viajes"): ?>
        <section class="admin-panel">
            <div class="panel-heading">
                <div>
                    <span>Solo lectura</span>
                    <h2>Viajes de la ruta</h2>
                </div>
            </div>

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
                    </div>
                <?php endforeach; ?>

                <?php if (!$viajes): ?>
                    <p class="empty">Aún no hay viajes registrados para esta ruta.</p>
                <?php endif; ?>
            </div>
        </section>
    <?php endif; ?>
</main>
</body>
</html>
