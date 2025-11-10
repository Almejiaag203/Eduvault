<?php
session_start();
session_destroy(); // Destruir todas las variables de sesión
header("Location: ../login/login.php"); // Redirigir al login
exit;
?>