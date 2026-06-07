<?php
session_start();
include("../conexion.php");

if (isset($_SESSION["conductor_id"])) {
    header("Location: gps.php");
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $usuario = trim($_POST["usuario"] ?? "");
    $password = trim($_POST["password"] ?? "");

    $sql = "SELECT id, usuario, password, id_bus 
            FROM conductores 
            WHERE usuario = ?";

    $stmt = sqlsrv_query($conn, $sql, [$usuario]);

    if ($stmt && $conductor = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {

        if ($password === $conductor["password"]) {
            $_SESSION["conductor_id"] = $conductor["id"];
            $_SESSION["conductor_usuario"] = $conductor["usuario"];
            $_SESSION["id_bus"] = $conductor["id_bus"];

            header("Location: gps.php");
            exit;
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
        radial-gradient(circle at 20% 20%, rgba(37,244,255,.24), transparent 30%),
        radial-gradient(circle at 80% 80%, rgba(0,200,232,.16), transparent 35%);
}

.login-box{
    position:relative;
    z-index:2;
    width:92%;
    max-width:430px;
    padding:38px;
    border-radius:30px;
    background:rgba(15,23,42,.78);
    border:1px solid rgba(255,255,255,.1);
    backdrop-filter:blur(20px);
    box-shadow:0 25px 80px rgba(0,0,0,.5);
}

.logo{
    width:64px;
    height:64px;
    display:grid;
    place-items:center;
    border-radius:20px;
    background:linear-gradient(135deg,#25f4ff,#00c8e8);
    color:#020617;
    font-size:30px;
    margin-bottom:22px;
}

h1{
    margin:0 0 8px;
    font-size:32px;
}

p{
    margin:0 0 28px;
    color:#94a3b8;
}

input{
    width:100%;
    padding:15px 18px;
    margin-bottom:14px;
    border:none;
    outline:none;
    border-radius:16px;
    background:rgba(255,255,255,.08);
    border:1px solid rgba(255,255,255,.1);
    color:white;
    font-size:15px;
}

input::placeholder{
    color:#94a3b8;
}

button{
    width:100%;
    padding:15px;
    border:none;
    border-radius:999px;
    cursor:pointer;
    font-weight:900;
    background:linear-gradient(135deg,#25f4ff,#00c8e8);
    color:#020617;
    margin-top:8px;
}

.error{
    background:rgba(239,68,68,.12);
    border:1px solid rgba(239,68,68,.35);
    color:#fecaca;
    padding:12px;
    border-radius:14px;
    margin-bottom:16px;
    font-size:14px;
}

.back{
    display:block;
    text-align:center;
    color:#94a3b8;
    text-decoration:none;
    margin-top:20px;
    font-size:14px;
}

.back:hover{
    color:#25f4ff;
}
</style>
</head>

<body>

<div class="login-box">
    <div class="logo">🚌</div>

    <h1>Conductor</h1>
    <p>Inicia sesión para compartir ubicación GPS.</p>

    <?php if($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="text" name="usuario" placeholder="Usuario" required>
        <input type="password" name="password" placeholder="Contraseña" required>
        <button type="submit">Ingresar al GPS</button>
    </form>

    <a href="../index.php" class="back">Volver al inicio</a>
</div>

</body>
</html>