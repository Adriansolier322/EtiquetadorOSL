<?php
include 'config.php';

// Inicializa el mensaje de error
$error = '';

// Mirar si el usuario ya está logueado
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header("Location: EtiquetadorOSL/index.php");
    exit;
}

// Procesar login
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Miramos si los campos están vacíos
    if (empty($username) || empty($password)) {
        $error = "Por favor, complete todos los campos.";
    } else {
        try {
            // Encontrar el usuario en la base de datos, incluyendo su rol_id
            $stmt = $pdo->prepare("SELECT id, username, password, rol_id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Verificar si el usuario existe y su contraseña es correcta
            if ($user && password_verify($password, $user['password'])) {

                   $_SESSION['loggedin'] = true;
                   $_SESSION['user_id'] = $user['id'];
                   $_SESSION['username'] = $user['username'];
                   $_SESSION['rol_id'] = $user['rol_id']; // Store user role

                   // Redireccionar al usuario a la página principal
                   header("Location: EtiquetadorOSL/index.php");
                   exit; // Paramos el script después de redirigir
            } else {
                $error = "Credenciales incorrectas.";
            }
        } catch (PDOException $e) {
            //Si hay un error guardarlo, pero no mostrar información sensible al usuario
            $error = "Error al conectar con la base de datos o al procesar la solicitud.";
        }
    }
}
?>
