<?php
session_start();
include("../conexion.php");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: login.php");
    exit;
}

$usuario = trim($_POST["usuario"] ?? "");
$password = trim($_POST["password"] ?? "");

$sql = "SELECT id_admin, usuario, password, id_ruta
        FROM administradores
        WHERE usuario = ?";

$stmt = sqlsrv_query($conn, $sql, [$usuario]);
$admin = $stmt ? sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC) : null;

if ($admin && $password === $admin["password"]) {
    $_SESSION["admin_id"] = $admin["id_admin"];
    $_SESSION["admin"] = $admin["usuario"];
    $_SESSION["id_ruta"] = $admin["id_ruta"];

    header("Location: dashboard.php");
    exit;
}

$_SESSION["admin_login_error"] = "Usuario o contraseña incorrectos.";
header("Location: login.php");
exit;
