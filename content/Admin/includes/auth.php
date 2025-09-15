<?php
include 'config.php';

/**
 * Revisa si el usuario está logueado.
 *
 * @return bool Verdadero si está logueado, falso en caso contrario.
 */

function isLoggedIn() {
    return isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
}

// Redirigir si no está logueado

// Funciones de autenticación seguras
// Este archivo debe incluirse en la parte superior de cualquier página que requiera que un usuario esté logueado,
// especialmente para el acceso a nivel de administrador.

// Iniciar sesión si no se ha iniciado ya
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * Revisa si el usuario logueado tiene el rol de administrador.
 * Se asume que un administrador tiene un role_id de 1.
 *
 * @return bool Verdadero si el usuario es un administrador, falso en caso contrario.
 */
function isAdministrator() {
    return isLoggedIn() && isset($_SESSION['role_id']) && $_SESSION['role_id'] == 1;
}

/**
 * Redirecciona a la página de login si el usuario no está logueado o no es administrador.
 */
function checkAuth() {
    // Si el usuario no es un administrador, destruir la sesión y redirigirlo.
    if (!isAdministrator() || !isLoggedIn()){
        // Destruir la sesión para asegurar que no queden credenciales
        session_destroy();
        // Redirigir a la página de login externa.
        header("Location: ../EtiquetadorOSL/index.php");
        // Salir para detener la ejecución del script inmediatamente
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
            $stmt = $pdo->prepare("SELECT id, username, password, role_id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Verificar si el usuario existe y la contraseña es correcta
            if ($user && password_verify($password, $user['password'])) {
                // Iniciar sesión
                $_SESSION['loggedin'] = true;
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role_id'] = $user['role_id'];
                // Redirigir según el rol
                // Si es administrador, ir a admin/index.php
                // Si no, destruir la sesión y redirigir a la página de login externa
                // para evitar acceso no autorizado
                // Esto asegura que solo los administradores puedan acceder al área de administración
                // y que otros usuarios sean redirigidos adecuadamente.
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