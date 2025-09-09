<?php
/**
Hay que instalar composer,
sudo apt install composer
Asegúrate de tenerlos en la ruta correcta
composer require phpmailer/phpmailer
Si Composer muestra un error de conflicto de dependencias con Google Authenticator,
es posible que tengas que eliminar esa dependencia con el comando:
composer remove phpgangsta/googleauthenticator
*/

// Incluir archivos necesarios
require 'includes/config.php';

require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMessage = "Por favor, ingrese un correo electrónico válido.";
    } else {
        try {
            // Verificar si el email existe
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                // Generar un token único
                $token = bin2hex(random_bytes(32));
                // Establecer una fecha de caducidad (ej. 1 hora a partir de ahora)
                $expires = date("Y-m-d H:i:s", strtotime('+1 hour'));

                // Guardar el token en la tabla password_resets
                $insertStmt = $pdo->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)");
                $insertStmt->execute([$user['id'], $token, $expires]);

                // Crear el enlace de restablecimiento
                $resetLink = "http://localhost/reset_password.php?token=" . $token;

                // --- CONFIGURACIÓN DE PHPMailer ---
                $mail = new PHPMailer(true);
                // Configuración de SMTP
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com'; // Servidor SMTP; NECESITA SER UNO REAL
                $mail->SMTPAuth = true;
                $mail->Username = 'noreply.etiquetadorosl@gmail.com'; // CORREO
                $mail->Password = '----'; // CONTRASEÑA, por motivos de seguridad NO se mostrará
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                // Destinatarios
                $mail->setFrom('-----', 'etiquetadorOSL');
                $mail->addAddress($email);

                // Contenido
                $mail->isHTML(true);
                $mail->Subject = "Restablecimiento de Contraseña";
                $mail->Body    = "Haga clic en el siguiente enlace para restablecer su contraseña: <a href='{$resetLink}'>{$resetLink}</a>";
                
                $mail->send();

                $successMessage = "Se ha enviado un correo electrónico con las instrucciones para restablecer su contraseña.";
            } else {
                $errorMessage = "El correo electrónico no se encuentra en nuestra base de datos.";
            }
        } catch (Exception $e) {
            $errorMessage = "Error al enviar el correo: " . $mail->ErrorInfo;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Olvidé mi Contraseña</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="main-container">
        <h1>Restablecer Contraseña</h1>
        <?php if (!empty($successMessage)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($successMessage); ?></div>
        <?php endif; ?>
        
        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($errorMessage); ?></div>
        <?php endif; ?>
        
        <div class="card">
            <h2>Ingrese su Correo Electrónico</h2>
            <form method="post">
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <button type="submit" class="btn">Enviar Enlace</button>
            </form>
        </div>
        <div style="text-align: center; margin-top: 15px;">     
            <p><a href="login.php" class="btn">Volver a login</a></p>
        </div>
    </div>
</body>
</html>

