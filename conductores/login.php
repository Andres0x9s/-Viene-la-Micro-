<?php
session_start();
include("../conexion.php");

/*
Flujo correcto:
- Si ya tiene viaje activo en sesión -> gps.php
- Si está logeado pero aún no eligió bus -> seleccionar_bus.php
- Si no está logeado -> mostrar login
*/

if (isset($_SESSION["conductor_id"]) && isset($_SESSION["id_bus"]) && isset($_SESSION["id_viaje"])) {
    header("Location: gps.php");
    exit;
}

if (isset($_SESSION["conductor_id"]) && isset($_SESSION["id_ruta"])) {
    header("Location: seleccionar_bus.php");
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $usuario = trim($_POST["usuario"] ?? "");
    $password = trim($_POST["password"] ?? "");

    $sql = "SELECT id, usuario, password, id_ruta, estado
            FROM conductores 
            WHERE usuario = ?";

    $stmt = sqlsrv_query($conn, $sql, [$usuario]);

    if ($stmt && $conductor = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {

        if (($conductor["estado"] ?? "") !== "activo") {
            $error = "Este conductor se encuentra inactivo.";
        } elseif ($password === $conductor["password"]) {

            if (empty($conductor["id_ruta"])) {
                $error = "Este conductor no tiene una línea asignada.";
            } else {
                $_SESSION["conductor_id"] = $conductor["id"];
                $_SESSION["conductor_usuario"] = $conductor["usuario"];
                $_SESSION["id_ruta"] = $conductor["id_ruta"];

                unset($_SESSION["id_bus"]);
                unset($_SESSION["id_viaje"]);
                unset($_SESSION["direccion"]);

                header("Location: seleccionar_bus.php");
                exit;
            }

        } else {
            $error = "Usuario o contraseña incorrectos.";
        }

    } else {
        $error = "Usuario o contraseña incorrectos.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login Conductor - Viene La Micro</title>

<link rel="icon" type="image/png" href="../busicono.png">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/login.css">
</head>

<body>

<div class="login-box">
    <div class="logo">🚌</div>

    <h1>Conductor</h1>
    <p>Inicia sesión para seleccionar tu bus y compartir ubicación GPS.</p>

    <?php if($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="text" name="usuario" placeholder="Usuario" required>
        <input type="password" name="password" placeholder="Contraseña" required>
        <button type="submit">Ingresar</button>
    </form>

    <a href="../index.php" class="back">Volver al inicio</a>
</div>

</body>
</html>


