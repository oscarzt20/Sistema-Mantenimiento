<?php
    // Variables necesarias para la BD
    $host = "localhost";
    $user = "root";
    $password = "";
    $database = "mantenimientobd";

    // constructor
    $connection = new mysqli($host, $user, $password, $database);

    if ($connection->connect_error)
    {
        // Método die
        die("Connection failed".$connection->connect_error);
    }
?>