<?php
session_start();

unset($_SESSION["admin_id"], $_SESSION["admin"], $_SESSION["id_ruta"]);

header("Location: login.php");
exit;
