<?php
require_once 'includes/config.php';

session_start();


$errorMessage = '';
$successMessage = '';
$username = ''; // Inicializamos para prevenir "Undefined variable" en el valor del formulario

// Procesar el formulario cuando se envía a través de POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitizar y recortar entradas de usuario
    $username = trim($_POST['username'] ?? ''); // Usa null coalescing operator para mayor seguridad
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
            // --- Paso 1: Mirar si el usuario ya existe ---
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
            $checkStmt->execute([$username]);
            $userExists = $checkStmt->fetchColumn(); //Obtiene el conteo

            if ($userExists > 0) {
                // Si el usuario existe, establecer mensaje de error
                $errorMessage = 'El nombre de usuario ya está en uso. Por favor, elige otro.';
            } else {
                // --- Paso 2: Si el nombre de usuario está disponible, hashear la contraseña e insertar el nuevo usuario ---
                $passwordHash = password_hash($password, PASSWORD_DEFAULT); // <--- SE HASHEA LA CONTRASEÑA AQUÍ

                // Insertamos el nuevo usuario en la base de datos
                // Suponiendo que 'id_rol' tiene un valor predeterminado en la base de datos o siempre se establece en 2 para nuevos registros
                $insertStmt = $pdo->prepare("INSERT INTO users(username, password, id_rol) VALUES(?, ?, ?)");
                $insertStmt->execute([$username, $passwordHash, 2]); // Usando 2 como id_rol predeterminado(usuario)

                // Redirigir a la página de inicio de sesión con una bandera de éxito
                $_SESSION['registration_success'] = '¡Registro exitoso! Ahora puedes iniciar sesión.';
                header('Location: login.php');
                exit(); // Siempre salir después de una redirección
            }
        }  catch (PDOException $e) {
            error_log("Registration PDO Error: " . $e->getMessage()); // Keep this for server-side logging
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Usuario - Panel de Acceso</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="login-container">
        <h1>Crear una cuenta</h1>
        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($errorMessage); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($successMessage)): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($successMessage); ?>
            </div>
        <?php endif; ?>

        <form action="register.php" method="POST">
            <div class="form-group">
                <label for="username">Nombre de usuario:</label>
                <input type="text" name="username" id="username" required value="<?php echo htmlspecialchars($username); ?>">
            </div>
            <div class="form-group">
                <label for="password">Contraseña:</label>
                <input type="password" name="password" id="password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirmar contraseña:</label>
                <input type="password" name="confirm_password" id="confirm_password" required>
            </div>
            <button type="submit">Registrarse</button>
        </form>
        <div style="text-align: center; margin-top: 20px;">
            <p>¿Ya tienes una cuenta? <a href="login.php">Inicia sesión aquí</a></p>
        </div>
    </div>
</body>
</html>