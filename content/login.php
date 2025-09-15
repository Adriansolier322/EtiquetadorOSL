<?php include 'includes/auth.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Panel de Acceso</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="main-container">
        <h1>Panel de acceso</h1>
        <?php if(isset($_GET['registered'])): ?>
            <div class="alert alert-success">¡Registro completado! Ya puedes iniciar sesión.</div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <form action="login.php" method="post">
            <div class="form-group">
                <label for="username">Usuario:</label>
                <input type="text" id="username" name="username" required autofocus>
            </div>
            <div class="form-group">
                <label for="password">Contraseña:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" name="login" class="btn">Iniciar Sesión</button>
        </form>
        <div style="text-align: center; margin-top: 15px;">
            <p>¿No tienes una cuenta? <a href="register.php">Regístrate aquí</a></p>
        </div>
        <div style="text-align: center; margin-top: 15px;">
            <p>¿Olvidaste tu contraseña? <a href="forgot_password.php">Recupérala aquí</a></p>
        </div>
    </div>
</body>
</html>