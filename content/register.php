<?php
// Display all errors for development (remove in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'includes/config.php'; // Contains your $pdo connection

$errorMessage = '';
$successMessage = '';
$username = '';
$email = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and trim user inputs
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');

    // Basic Validations
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $errorMessage = 'Todos los campos son obligatorios. Por favor, completa todos los campos.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMessage = 'Por favor, introduce una dirección de correo electrónico válida.';
    } elseif ($password !== $confirm_password) {
        $errorMessage = 'Las contraseñas no coinciden.';
    } elseif (strlen($username) < 3 || strlen($username) > 50) {
        $errorMessage = 'El nombre de usuario debe tener entre 3 y 50 caracteres.';
    } elseif (strlen($password) < 8) {
        $errorMessage = 'La contraseña debe tener al menos 8 caracteres.';
    } else {
        try {
            // --- Step 1: Check if the user or email already exists ---
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ?");
            $checkStmt->execute([$username, $email]);
            $userExists = $checkStmt->fetchColumn();

            if ($userExists > 0) {
                $errorMessage = 'El nombre de usuario o el email ya están en uso. Por favor, elige otros.';
            } else {
                // --- Step 2: If credentials are available, hash password and insert new user ---
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);

                // Insert the new user into the database
                // Assuming 'role_id' is always 2 for new registrations
                $insertStmt = $pdo->prepare("INSERT INTO users(username, email, password, role_id) VALUES(?, ?, ?, ?)");
                $insertStmt->execute([$username, $email, $passwordHash, 2]);

                // Set success message in session and redirect
                $_SESSION['registration_success'] = '¡Registro exitoso! Ahora puedes iniciar sesión.';
                header('Location: login.php');
                exit(); // Always exit after a redirect
            }
        } catch (PDOException $e) {
            error_log("Registration PDO Error: " . $e->getMessage());
            $errorMessage = 'Hubo un problema con el registro. Por favor, inténtalo de nuevo más tarde.' . $e->getMessage();
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
    <div class="main-container">
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
                <label for="email">Email:</label>
                <input type="email" name="email" id="email" required value="<?php echo htmlspecialchars($email); ?>">
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
