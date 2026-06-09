<?php

session_start();

if (!isset($_SESSION["admin"]) || !isset($_SESSION["id_ruta"])) {

    header("Location: login.php");
    exit;

}
