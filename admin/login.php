<!DOCTYPE html>
<html>
<head>
    <title>Login Administrador</title>
</head>
<body>

<form action="validar.php" method="POST">

    <input
        type="text"
        name="usuario"
        placeholder="Usuario"
        required
    >

    <input
        type="password"
        name="password"
        placeholder="Contraseña"
        required
    >

    <button type="submit">
        Ingresar
    </button>

</form>

</body>
</html>