<?php
// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_USER', 'etiquetador');
define('DB_PASS', 'password');
define('DB_NAME', 'etiquetador');

// Iniciar sesión
session_start();

// Conexión a la base de datos
try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("ERROR: No se pudo conectar. " . $e->getMessage());
}
?>