<?php
// Incluir archivos necesarios
require 'includes/config.php';

$successMessage = '';
$errorMessage = '';
$user = null;
$token = $_GET['token'] ?? '';

// Verificar si se proporcionó un token
if (!empty($token)) {
    // Buscar el token en la tabla password_resets
    $stmt = $pdo->prepare("SELECT user_id, expires_at FROM password_resets WHERE token = ?");
    $stmt->execute([$token]);
    $resetData = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($resetData && strtotime($resetData['expires_at']) > time()) {
        // El token es válido y no ha expirado
        $userStmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $userStmt->execute([$resetData['user_id']]);
        $user = $userStmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $errorMessage = "Error al encontrar el usuario asociado al token.";
        }
    } else {
        $errorMessage = "Token inválido o expirado.";
    }
} else {
    $errorMessage = "Token no proporcionado.";
}

// Procesar el formulario de nueva contraseña
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $user) {
    $newPassword = $_POST['new_password'];

    if (empty($newPassword)) {
        $errorMessage = "Por favor, ingrese una nueva contraseña.";
    } else {
        try {
            $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
            
            // Actualizar la contraseña
            $updateStmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $updateStmt->execute([$passwordHash, $user['id']]);

            // Eliminar el token de restablecimiento de la base de datos
            $deleteStmt = $pdo->prepare("DELETE FROM password_resets WHERE token = ?");
            $deleteStmt->execute([$token]);
            
            $successMessage = "Contraseña actualizada correctamente. Ahora puede iniciar sesión.";
            $user = null; // Ocultar el formulario
            
        } catch (PDOException $e) {
            $errorMessage = "Error al actualizar la contraseña: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="main-content">
        <h1>Restablecer Contraseña</h1>
        <?php if (!empty($successMessage)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($successMessage); ?></div>
        <?php endif; ?>
        
        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($errorMessage); ?></div>
        <?php endif; ?>
        
        <?php if ($user && empty($successMessage)): ?>
            <div class="card">
                <h2>Ingrese su Nueva Contraseña</h2>
                <form method="post">
                    <div class="form-group">
                        <label for="new_password">Nueva Contraseña:</label>
                        <input type="password" id="new_password" name="new_password" required>
                    </div>
                    <button type="submit" class="btn">Cambiar Contraseña</button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
