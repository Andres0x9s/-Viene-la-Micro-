<?php

session_start();

if (!isset($_SESSION["admin"])) {

    header("Location: login.php");
    exit;

}

$rolAdmin = $_SESSION["admin_rol"] ?? "ruta";

if ($rolAdmin !== "super" && !isset($_SESSION["id_ruta"])) {

    header("Location: login.php");
    exit;

}
