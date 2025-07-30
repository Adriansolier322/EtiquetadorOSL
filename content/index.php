<?php
require_once 'includes/config.php'; 
include 'includes/auth.php';

session_start();

$errorMessage = '';
$successMessage = '';
$username = ''; // Inicializado para evitar "Undefined variable" en el valor del formulario
$showLoginForm = true; // Inicializado para mostrar el formulario de inicio de sesión


if (isset($_SESSION['registration_success']) && $_SESSION['registration_success'] === true) {
    // Determine which form was submitted based on the button name
    if (isset($_POST['login'])) {
        // --- Login ---
        $showLoginForm = true; // Quedarse en el login si hay problemas o éxito
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if (empty($username) || empty($password)) {
            $errorMessage = 'Por favor, introduce tu nombre de usuario y contraseña.';
        } else {
            try {
                $stmt = $pdo->prepare("SELECT id, username, password FROM users WHERE username = ?");
                $stmt->execute([$username]);
                $user = $stmt->fetch();

                if ($user && password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    header('Location: EtiquetadorOSL/index.php'); // Redirigir al etiquetador
                    exit();
                } else {
                    $errorMessage = 'Nombre de usuario o contraseña incorrectos.';
                }
            } catch (PDOException $e) {
                error_log("Login PDO Error: " . $e->getMessage());
                $errorMessage = 'Error en el servidor al intentar iniciar sesión. Por favor, inténtalo de nuevo más tarde.';
            }
        }
    } elseif (isset($_POST['register'])) {
        // --- Registro ---
        $showLoginForm = false; // Quedarse en el formulario de registro si hay problemas
        $username = trim($_POST['username'] ?? ''); // Usar el operador de fusión null para mayor seguridad
        $password = trim($_POST['password'] ?? '');
        $confirm_password = trim($_POST['confirm_password'] ?? '');

        // Validaciones básicas
        if (empty($username) || empty($password) || empty($confirm_password)) {
            $errorMessage = 'Todos los campos son obligatorios. Por favor, completa todos los campos.';
        } elseif ($password !== $confirm_password) {
            $errorMessage = 'Las contraseñas no coinciden.';
        } elseif (strlen($username) < 3 || strlen($username) > 50) {
            $errorMessage = 'El nombre de usuario debe tener entre 3 y 50 caracteres.';
        } elseif (strlen($password) < 8) {
            $errorMessage = 'La contraseña debe tener al menos 8 caracteres.';
        } else {
            try {
                // --- Paso 1: Mirar si el usuario existe ---
                $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
                $checkStmt->execute([$username]);
                $userExists = $checkStmt->fetchColumn(); // Obtener el conteo

                if ($userExists > 0) {
                    // Si el usuario existe, establecer un mensaje de error
                    $errorMessage = 'El nombre de usuario ya está en uso. Por favor, elige otro.';
                } else {
                    // --- Paso 2: Si el nombre de usuario está disponible, hashear la contraseña e insertar el nuevo usuario ---
                    $passwordHash = password_hash($password, PASSWORD_DEFAULT); // <--- PASSWORD HASHED HERE

                    // Insertar el nuevo usuario en la base de datos
                    $insertStmt = $pdo->prepare("INSERT INTO users(username, password, id_rol) VALUES(?, ?, ?)");
                    $insertStmt->execute([$username, $passwordHash, 2]); // Using 2 as default id_rol (user)

                    // Establecer mensaje de éxito y cambiar al formulario de inicio de sesión
                    $successMessage = '¡Registro exitoso! Ahora puedes iniciar sesión.';
                    $showLoginForm = true;
                    $username = ''; // Limpiar el campo de nombre de usuario para el formulario de inicio de sesión
                }
            }  catch (PDOException $e) {
                error_log("Registration PDO Error: " . $e->getMessage()); // Keep this for server-side logging
                $errorMessage = 'Hubo un error al registrar tu cuenta. Por favor, inténtalo de nuevo.';
            }
        }
    }
} else {
    // Mirar si hay un mensaje de éxito de un redireccionamiento de registro anterior
    if (isset($_SESSION['registration_success'])) {
        $successMessage = $_SESSION['registration_success'];
        unset($_SESSION['registration_success']); // Limpiar después de mostrar
        $showLoginForm = true; // Asegurarse de que se muestre el formulario de inicio de sesión
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido al etiquetador OSL</title>
    <link rel="stylesheet" href="assets/css/style.css"> </head>
<body>
    <div class="main-container">
        <div class="content-wrapper">

            <div class="welcome-section">
                <h1 style="color: #2ef13; font-size: 2.8em; text-align: center;">¡Bienvenido!</h1>
                <p style="color: #fff; font-size: 1.2em; text-align: center;">Accede a tu cuenta o regístrate para empezar.</p>
            </div>

            <div id="login-section" class="login-container">
                <h1>Panel de acceso</h1>
                <?php if (!empty($errorMessage) && $showLoginForm): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($errorMessage); ?></div>
                <?php endif; ?>
                <?php if (!empty($successMessage) && $showLoginForm): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($successMessage); ?></div>
                <?php endif; ?>
                <form action="" method="post">
                    <div class="form-group">
                        <label for="login-username">Usuario:</label>
                        <input type="text" id="login-username" name="username" required autofocus value="<?php echo htmlspecialchars($showLoginForm ? $username : ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="login-password">Contraseña:</label>
                        <input type="password" id="login-password" name="password" required>
                    </div>
                    <button type="submit" name="login">Iniciar Sesión</button>
                </form>
                <p>¿No tienes una cuenta? <a href="#" onclick="showRegisterForm(event)">Regístrate aquí</a></p>
            </div>

            <div id="register-section" class="login-container" style="display: none;"> <h1>Crear una cuenta</h1>
                <?php if (!empty($errorMessage) && !$showLoginForm): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($errorMessage); ?></div>
                <?php endif; ?>
                <?php if (!empty($successMessage) && !$showLoginForm): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($successMessage); ?></div>
                <?php endif; ?>
                <form action="" method="POST">
                    <div class="form-group">
                        <label for="register-username">Nombre de usuario:</label>
                        <input type="text" name="username" id="register-username" required value="<?php echo htmlspecialchars(!$showLoginForm ? $username : ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="register-password">Contraseña:</label>
                        <input type="password" name="password" id="register-password" required>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirmar contraseña:</label>
                        <input type="password" name="confirm_password" id="confirm_password" required>
                    </div>
                    <button type="submit" name="register">Registrarse</button>
                </form>
                <p>¿Ya tienes una cuenta? <a href="#" onclick="showLoginForm(event)">Inicia sesión aquí</a></p>
            </div>
        </div>
    </div>

    <script src="assets/js/script.js"></script> <script>
        // Sirve para que el formulario correcto se muestre inicialmente basado en la lógica del servidor.
        document.addEventListener('DOMContentLoaded', (event) => {
            <?php if ($showLoginForm): ?>
                document.getElementById('login-section').style.display = 'block';
                document.getElementById('register-section').style.display = 'none';
            <?php else: ?>
                document.getElementById('login-section').style.display = 'none';
                document.getElementById('register-section').style.display = 'block';
            <?php endif; ?>

            // Si hay un mensaje de error, asegurarse de que se muestre el formulario correcto
            <?php if (!empty($errorMessage)): ?>
                <?php if ($showLoginForm): ?>
                    document.getElementById('login-section').style.display = 'block';
                    document.getElementById('register-section').style.display = 'none';
                <?php else: ?>
                    document.getElementById('login-section').style.display = 'none';
                    document.getElementById('register-section').style.display = 'block';
                <?php endif; ?>
            <?php endif; ?>
        });
    </script>
</body>
</html>