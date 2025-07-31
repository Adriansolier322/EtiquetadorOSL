<?php
// Display all errors for development (remove in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'includes/config.php'; // Contains your $pdo connection

$errorMessage = '';
$successMessage = '';
$username = ''; // Initialize for form value

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and trim user inputs
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');

    // Basic Validations
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
            // --- Step 1: Check if the user already exists ---
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
            $checkStmt->execute([$username]);
            $userExists = $checkStmt->fetchColumn();

            if ($userExists > 0) {
                // If user exists, set error message
                $errorMessage = 'El nombre de usuario ya está en uso. Por favor, elige otro.';
            } else {
                // --- Step 2: If username is available, hash password and insert new user ---
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);

                // Insert the new user into the database
                // Assuming 'role_id' has a default value in the database or is always set to 2 for new registrations
                $insertStmt = $pdo->prepare("INSERT INTO users(username, password, role_id) VALUES(?, ?, ?)");
                // The third parameter '2' assigns the default user role. Adjust if your roles are different.
                $insertStmt->execute([$username, $passwordHash, 2]);

                // Set success message in session and redirect
                $_SESSION['registration_success'] = '¡Registro exitoso! Ahora puedes iniciar sesión.';
                header('Location: login.php');
                exit(); // Always exit after a redirect
            }
        } catch (PDOException $e) {
            // Log the error for debugging. NEVER display $e->getMessage() directly to the user in production.
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