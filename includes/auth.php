<?php
// This line includes config.php, which already calls session_start()
include 'config.php';

// Initialize error message variable
$error = '';

// Check if the user is already logged in (optional, but good practice)
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header("Location: EtiquetadorOSL/index.php");
    exit;
}

// Procesar login
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Validate that fields are not empty
    if (empty($username) || empty($password)) {
        $error = "Por favor, complete todos los campos.";
    } else {
        try {
            // Find the user in the database, including their rol_id
            $stmt = $pdo->prepare("SELECT id, username, password, rol_id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Verify if user exists and password is correct
            if ($user && password_verify($password, $user['password'])) {
               // Check if rol_id is set and is 1 (admin)
               //if(isset($user['rol_id']) && $user['rol_id'] == 1){
                   $_SESSION['loggedin'] = true;
                   $_SESSION['user_id'] = $user['id'];
                   $_SESSION['username'] = $user['username'];
                   $_SESSION['rol_id'] = $user['rol_id']; // Store user role

                   // Redirect the user
                   header("Location: EtiquetadorOSL/index.php");
                   exit; // Crucial to stop script execution after redirect
              // } else {
             //      $error = "No tienes permisos para acceder a esta área.";
               //}
            } else {
                $error = "Credenciales incorrectas.";
            }
        } catch (PDOException $e) {
            // Log the error for debugging, but don't show sensitive info to user
            // error_log("Database Error: " . $e->getMessage());
            $error = "Error al conectar con la base de datos o al procesar la solicitud.";
        }
    }
}
?>
