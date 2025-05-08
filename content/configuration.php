<?php

// Datos de configuracion de acceso mysql
$serverip = "localhost";  // Poner aqui la ip del servidor (ej: localhost)
$username = "username";   // Nombre de usuario con acceso a la base de datos
$password = "password";   // Contraseña del usuario con acceso a la base de datos
$dbname = "database_name";      // Nombre de la base de datos


// Conexion con la base de datos
try {
  $conn = new PDO("mysql:host=$serverip;dbname=$dbname", $username, $password);
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
  echo "Connection failed: " . $e->getMessage();
}
?> 
