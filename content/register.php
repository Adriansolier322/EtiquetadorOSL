<?php
require_once 'includes/config.php'; // Contiene tu $pdo conexión
// Variables para mensajes y datos del formulario
$errorMessage = '';
$successMessage = '';
$username = '';
$email = '';

// Proceso de envío del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitiza y valida entradas
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');

    // Validaciones básicas
    //Si algún campo está vacío
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $errorMessage = 'Todos los campos son obligatorios. Por favor, completa todos los campos.';
    } 
    //Si el email no es válido
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMessage = 'Por favor, introduce una dirección de correo electrónico válida.';
    } 
    //Si las contraseñas no coinciden
    elseif ($password !== $confirm_password) {
        $errorMessage = 'Las contraseñas no coinciden.';
    } 
    //Si el nombre de usuario no tiene entre 3 y 50 caracteres
    elseif (strlen($username) < 3 || strlen($username) > 50) {
        $errorMessage = 'El nombre de usuario debe tener entre 3 y 50 caracteres.';
    } 
    //Si la contraseña no tiene al menos 12 caracteres
    elseif (strlen($password) < 12) {
        $errorMessage = 'La contraseña debe tener al menos 12 caracteres.';
    } else {
        try {
            // Verificar si el usuario o el email ya existen
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ?");
            $checkStmt->execute([$username, $email]);
            $userExists = $checkStmt->fetchColumn();

            if ($userExists > 0) {
                $errorMessage = 'El nombre de usuario o el email ya están en uso. Por favor, elige otros.';
            } else {
                // --- Si las credenciales son válidas, hashea la contraseña e inserta el nuevo usuario ---
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);

                // Insertamos el nuevo usuario en la base de datos
                // Suponemos que 'role_id' es siempre 2 para nuevos registros
                $insertStmt = $pdo->prepare("INSERT INTO users(username, email, password, role_id) VALUES(?, ?, ?, ?)");
                $insertStmt->execute([$username, $email, $passwordHash, 2]);

                // Poner el mensaje de éxito en la sesión y redirigir
                $_SESSION['registration_success'] = '¡Registro exitoso! Ahora puedes iniciar sesión.';
                header('Location: login.php');
                exit(); // Siempre salir después de una redirección
            }
        } 
        //Si hay un error con la base de datos
        catch (PDOException $e) {
            error_log("Registration PDO Error: " . $e->getMessage());
            $errorMessage = 'Hubo un problema con el registro. Por favor, inténtalo de nuevo más tarde.';
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
