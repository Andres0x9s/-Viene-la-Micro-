<?php
session_start();
include("../conexion.php");
include("../helpers/passwords.php");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: login.php");
    exit;
}

$usuario = trim($_POST["usuario"] ?? "");
$password = trim($_POST["password"] ?? "");

$rolColumnStmt = sqlsrv_query($conn, "SELECT COL_LENGTH('administradores', 'rol') AS existe_rol");
$rolColumnRow = $rolColumnStmt ? sqlsrv_fetch_array($rolColumnStmt, SQLSRV_FETCH_ASSOC) : null;
$tieneRol = !empty($rolColumnRow["existe_rol"]);

if (!$tieneRol) {
    sqlsrv_query($conn, "ALTER TABLE administradores ADD rol VARCHAR(20) NOT NULL CONSTRAINT DF_administradores_rol DEFAULT 'ruta'");
    $rolColumnStmt = sqlsrv_query($conn, "SELECT COL_LENGTH('administradores', 'rol') AS existe_rol");
    $rolColumnRow = $rolColumnStmt ? sqlsrv_fetch_array($rolColumnStmt, SQLSRV_FETCH_ASSOC) : null;
    $tieneRol = !empty($rolColumnRow["existe_rol"]);
}

$rolSelect = $tieneRol ? ", rol" : "";

$sql = "SELECT id_admin, usuario, password, id_ruta{$rolSelect}
        FROM administradores
        WHERE usuario = ?";

$stmt = sqlsrv_query($conn, $sql, [$usuario]);
$admin = $stmt ? sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC) : null;

if ($admin && verifyUserPassword($conn, "administradores", "id_admin", $admin["id_admin"], $password, $admin["password"])) {
    $_SESSION["admin_id"] = $admin["id_admin"];
    $_SESSION["admin"] = $admin["usuario"];
    $_SESSION["admin_rol"] = $admin["rol"] ?? "ruta";

    if ($_SESSION["admin_rol"] === "super") {
        unset($_SESSION["id_ruta"]);
    } else {
        $_SESSION["id_ruta"] = $admin["id_ruta"];
    }

    header("Location: dashboard.php");
    exit;
}

$_SESSION["admin_login_error"] = "Usuario o contraseña incorrectos.";
header("Location: login.php");
exit;

