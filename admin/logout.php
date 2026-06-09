<?php
session_start();

unset($_SESSION["admin_id"], $_SESSION["admin"], $_SESSION["id_ruta"], $_SESSION["admin_rol"]);

header("Location: login.php");
exit;
