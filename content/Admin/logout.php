<?php
session_start();           // Iniciar sesión
session_unset();           // Eliminar todas las variables de sesión
session_destroy();         // Destruir la sesión
header("Location: ../EtiquetadorOSL/index.php");  // Redirigir al login
exit;
?>
