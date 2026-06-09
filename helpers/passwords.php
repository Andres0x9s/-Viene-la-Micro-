<?php

function hashUserPassword($password)
{
    return password_hash($password, PASSWORD_DEFAULT);
}

function storedPasswordIsHash($storedPassword)
{
    $info = password_get_info((string) $storedPassword);
    return ($info["algo"] ?? 0) !== 0;
}

function updateStoredPasswordHash($conn, $table, $idColumn, $idValue, $plainPassword)
{
    $allowed = [
        "administradores" => "id_admin",
        "conductores" => "id"
    ];

    if (!isset($allowed[$table]) || $allowed[$table] !== $idColumn) {
        return false;
    }

    $newHash = hashUserPassword($plainPassword);
    $sql = "UPDATE {$table} SET password = ? WHERE {$idColumn} = ?";

    return (bool) sqlsrv_query($conn, $sql, [$newHash, $idValue]);
}

function verifyUserPassword($conn, $table, $idColumn, $idValue, $plainPassword, $storedPassword)
{
    $storedPassword = (string) $storedPassword;

    if (storedPasswordIsHash($storedPassword)) {
        if (!password_verify($plainPassword, $storedPassword)) {
            return false;
        }

        if (password_needs_rehash($storedPassword, PASSWORD_DEFAULT)) {
            updateStoredPasswordHash($conn, $table, $idColumn, $idValue, $plainPassword);
        }

        return true;
    }

    if (!hash_equals($storedPassword, $plainPassword)) {
        return false;
    }

    updateStoredPasswordHash($conn, $table, $idColumn, $idValue, $plainPassword);
    return true;
}
