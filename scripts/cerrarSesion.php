<?php
session_start();
session_unset();
session_destroy();
header("Location: ../pages/Login.php"); // Cambia 'login.php' por tu página de inicio de sesión
exit();
?>
