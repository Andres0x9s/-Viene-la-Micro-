<?php
session_start();

if (isset($_SESSION["admin"]) && (isset($_SESSION["id_ruta"]) || ($_SESSION["admin_rol"] ?? "") === "super")) {
    header("Location: dashboard.php");
    exit;
}

$error = $_SESSION["admin_login_error"] ?? "";
unset($_SESSION["admin_login_error"]);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login Administrador - Viene La Micro</title>
<link rel="icon" type="image/png" href="../busicono.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/admin.css">
</head>

<body class="login-page">
<main class="login-shell">
    <section class="login-card">
        <div class="login-logo">A</div>

        <span class="eyebrow">Administración</span>
        <h1>Panel Admin</h1>
        <p>Gestiona buses, conductores, horarios y estadísticas de tu ruta asignada.</p>

        <?php if ($error): ?>
            <div class="flash error"><?= htmlspecialchars($error, ENT_QUOTES, "UTF-8") ?></div>
        <?php endif; ?>

        <form action="validar.php" method="POST">
            <label>
                Usuario
                <input type="text" name="usuario" placeholder="Usuario" required>
            </label>

            <label>
                Contraseña
                <input type="password" name="password" placeholder="Contraseña" required>
            </label>

            <button type="submit">Ingresar</button>
        </form>

        <a class="back-link" href="../index.php">Volver al inicio</a>
    </section>
</main>
</body>
</html>
