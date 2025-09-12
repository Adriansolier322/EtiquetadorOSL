<?php
session_start();
include 'includes/config.php';
include 'includes/auth.php';
$error = '';

if (!isset($_SESSION['2fa_code']) || !isset($_SESSION['pending_user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['verificar'])) {
    $codigo_ingresado = trim($_POST['codigo']);
    
    // Verificar si el código ha expirado
    if (time() > $_SESSION['2fa_expires_at']) {
        $error = "El código ha expirado. Vuelve a iniciar sesión.";
        // Limpiar la sesión 2FA para obligar a un nuevo intento
        unset($_SESSION['2fa_code'], $_SESSION['2fa_expires_at'], $_SESSION['pending_user_id']);
    } elseif ($codigo_ingresado == $_SESSION['2fa_code']) {
        
        // Código correcto, completar el inicio de sesión
        $user_id = $_SESSION['pending_user_id'];
        
        try {
            $stmt = $pdo->prepare("SELECT id, username FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                $_SESSION['loggedin'] = true;
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                
                // Limpiar la sesión 2FA
                unset($_SESSION['2fa_code'], $_SESSION['2fa_expires_at'], $_SESSION['pending_user_id']);

                header("Location: EtiquetadorOSL/index.php");
                exit;
            } else {
                $error = "Error al completar la autenticación. Por favor, intente de nuevo.";
            }
        } catch (PDOException $e) {
            $error = "Error al conectar con la base de datos: " . $e->getMessage();
        }
        
    } else {
        $error = "Código incorrecto.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación 2FA</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="main-container">
        <h1>Verificación de Seguridad</h1>
        <p>Hemos enviado un código a su correo. Por favor, introdúcelo para continuar.</p>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form action="verificar_2fa.php" method="post">
            <div class="form-group">
                <label for="codigo">Código 2FA:</label>
                <input type="text" id="codigo" name="codigo" required autofocus>
            </div>
            <button type="submit" name="verificar" class="btn">Verificar</button>
        </form>
    </div>
</body>
</html>