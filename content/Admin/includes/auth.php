<?php
include 'config.php';

/**
 * Checks if a user is currently logged in.
 *
 * @return bool True if logged in, false otherwise.
 */

function isLoggedIn() {
    return isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
}

// Redirigir si no está logueado

// Secure Authentication Functions
// This file should be included at the very top of any page that requires a user to be logged in,
// especially for admin-level access.

// Start the session if it's not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * Checks if the logged-in user has the administrator role.
 * Assumes an admin has a role_id of 1.
 *
 * @return bool True if the user is an admin, false otherwise.
 */
function isAdministrator() {
    return isLoggedIn() && isset($_SESSION['role_id']) && $_SESSION['role_id'] == 1;
}

/**
 * Redirects the user to the login page if they are not authenticated as an administrator.
 * This function should be called at the very beginning of every secure page.
 */
function checkAuth() {
    // If the user is not an administrator, destroy the session and redirect them.
    if (!isAdministrator() || !isLoggedIn()){
        // Destroy the session to ensure no lingering credentials
        session_destroy();
        // Redirect to the external login page.
        header("Location: ../EtiquetadorOSL/index.php");
        // Exit to stop script execution immediately
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