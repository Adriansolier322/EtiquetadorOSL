<?php
// Always include your configuration first
require_once 'includes/config.php'; // Use require_once to prevent multiple inclusions

// For development: display all errors. REMOVE OR DISABLE IN PRODUCTION!
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$errorMessage = '';
$successMessage = '';

// Process the form only when it's submitted via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and trim user inputs
    $username = trim($_POST['username'] ?? ''); // Use null coalescing operator for safety
    $password = trim($_POST['password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');

    // Basic Validations
    if (empty($username) || empty($password) || empty($confirm_password)) {
        $errorMessage = 'Todos los campos son obligatorios. Por favor, completa todos los campos.';
    } elseif ($password !== $confirm_password) {
        $errorMessage = 'Las contraseñas no coinciden.';
    } elseif (strlen($username) < 3 || strlen($username) > 50) { // Example length validation
        $errorMessage = 'El nombre de usuario debe tener entre 3 y 50 caracteres.';
    } elseif (strlen($password) < 8) { // Example password strength validation
        $errorMessage = 'La contraseña debe tener al menos 8 caracteres.';
    } else {
        // Check if the username already exists
        try {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username LIMIT 1");
            // Correct way to bind named parameters
            $stmt->execute([':username' => $username]);

            if ($stmt->fetch()) {
                $errorMessage = 'El nombre de usuario ya está en uso. Por favor, elige otro.';
            } else {
                // If username is available, proceed with user creation
                // Hash the password for security
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);

                // Insert the new user into the database
                $insertStmt = $pdo->prepare("INSERT INTO users(username, password) VALUES(?, ?)");
                $insertStmt->execute([$username, $passwordHash]);

                // Redirect to the login page with a success flag
                // Using a session message is generally better than a GET parameter for success messages
                $_SESSION['registration_success'] = '¡Registro exitoso! Ahora puedes iniciar sesión.';
                header('Location: login.php');
                exit();
            }
        } catch (PDOException $e) {
            // Log the error for debugging purposes (e.g., to a file)
            error_log("Registration PDO Error: " . $e->getMessage());
            $errorMessage = 'Error al registrar el usuario. Por favor, inténtalo de nuevo más tarde.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Usuario - Panel de Administración</title>
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

        <?php if (!empty($successMessage)): // This part won't be used directly due to redirect, but good practice ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($successMessage); ?>
            </div>
        <?php endif; ?>

        <form action="register.php" method="POST">
            <div class="form-group">
                <label for="username">Nombre de usuario:</label>
                <input type="text" name="username" id="username" required value="<?php echo htmlspecialchars($username ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="password">Contraseña:</label>
                <input type="password" name="password" id="password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirmar contraseña:</label>
                <input type="password" name="confirm_password" id="confirm_password" required>
            </div>
            <button type="submit">Registrarse<a href="login.php"> </button>
        </form>
        <div style="text-align: center; margin-top: 20px;">
            <p>¿Ya tienes una cuenta? <a href="login.php">Inicia sesión aquí</a></p>
        </div>
    </div>
</body>
</html>

