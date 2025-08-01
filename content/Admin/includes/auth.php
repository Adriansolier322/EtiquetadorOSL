<?php
include 'config.php';

// Verificar si el usuario está logueado
function isLoggedIn() {
    return isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
}

// Redirigir si no está logueado
function checkAuth() {
    if (!isLoggedIn()) {   
        header("Location: ../index.php");
        exit;
    }
}

// Procesar login
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    // Validar que los campos no estén vacíos
    if (empty($username) || empty($password)) {
        $error = "Por favor, complete todos los campos";
    } else {
        try {
            // Buscar el usuario en la base de datos
            $stmt = $pdo->prepare("SELECT id, username, password FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Verificar si el usuario existe y la contraseña es correcta
            if ($user && password_verify($password, $user['password'])) {
                // Iniciar sesión
                $_SESSION['loggedin'] = true;
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role_id'] = $user['role_id'];
                if ($_SESSION['role_id'] == 1) {
                    header("Location: index.php");
                    exit;
                } else {
                    $error = "Acceso denegado. No tiene permisos de administrador.";
                    session_destroy();
                    header("Location: ../EtiquetadorOSL/index.php");
                    exit;
                }
            } else {
                $error = "Credenciales incorrectas";
            }
        } catch (PDOException $e) {
            $error = "Error al conectar con la base de datos: " . $e->getMessage();
        }
    }
}