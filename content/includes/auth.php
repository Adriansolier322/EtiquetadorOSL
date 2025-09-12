<?php
session_start();

require 'vendor/autoload.php';
require 'config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;


function isLoggedIn() {
    return isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
}

function checkAuth() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = "Por favor, complete todos los campos";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id, username, password, email FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                $codigo_2fa = rand(100000, 999999);
                $_SESSION['2fa_code'] = $codigo_2fa;
                $_SESSION['2fa_expires_at'] = time() + 300;
                $_SESSION['pending_user_id'] = $user['id'];

                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = '---';
                    $mail->Password = '---';
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                    $mail->Port = 465;

                    $mail->setFrom('---', 'EtiquetadorOSL');
                    $mail->addAddress($user['email'], $user['username']);
                    $mail->isHTML(false);
                    $mail->Subject = "Tu codigo de verificacion 2FA";
                    $mail->Body = "Hola, \n\nTu código de verificación es: " . $codigo_2fa . "\n\nEste código expira en 5 minutos.";
                    
                    $mail->send();
                    
                    header("Location: verificar_2fa.php");
                    exit;

                } catch (Exception $e) {
                    $error = "Error al enviar el correo de verificación. Por favor, intente de nuevo más tarde.";
                }

            } else {
                $error = "Credenciales incorrectas";
            }
        } catch (PDOException $e) {
            $error = "Error al conectar con la base de datos: ";
        }
    }
}
?>