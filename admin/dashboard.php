<?php

include("auth.php");

?>

<h1>
Bienvenido <?= $_SESSION["admin"] ?>
</h1>

<p>
Ruta asignada: <?= $_SESSION["id_ruta"] ?>
</p>

<a href="logout.php">
Cerrar sesión
</a>