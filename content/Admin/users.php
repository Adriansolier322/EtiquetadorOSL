<?php 
// Incluir archivos necesarios
require 'includes/auth.php';
require 'includes/config.php';

// Verificar autenticación
checkAuth();

// Obtener todos los usuarios
$usersQuery = $pdo->query("SELECT * FROM users ORDER BY username");
$users = $usersQuery->fetchAll(PDO::FETCH_ASSOC);

// Variables para mensajes
$successMessage = '';
$errorMessage = '';

// Procesar formulario de añadir, cambiar contraseña, o fail
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add'])) {
        $newUsername = trim($_POST['username']);
        $newPassword = $_POST['password'];
        $newEmail = trim($_POST['email']);
        
        // Validar campos
        if (empty($newUsername) || empty($newPassword) || empty($newEmail)) {
            $errorMessage = "Por favor, complete todos los campos.";
        } else {
            try {
                // Hash de la contraseña
                $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
                
                // Insertar nuevo usuario
                $insertStmt = $pdo->prepare("INSERT INTO users (username, password, email, role_id) VALUES (?, ?, ?, ?)");
                $insertStmt->execute([$newUsername, $passwordHash, $newEmail, 2]); // Asignar rol de usuario por defecto

                $successMessage = "Usuario añadido correctamente.";
                // Recargar la lista de usuarios
                header("Location: users.php");
                exit;
            } catch (PDOException $e) {
                $errorMessage = "Error al añadir usuario: " . $e->getMessage();
            }
        }
    } elseif (isset($_POST['newpass'])) {
        $newpass = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $userId = $_POST['userId'];
        // Validar campos
        if (empty($newpass)) {
            $errorMessage = "Por favor, complete todos los campos.";
        } else {
            try {
                $stmt = $pdo->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$newpass, $userId]);
                header("Location: users.php");
                exit;

            } catch (PDOException $e) {
                $errorMessage = "Error al cambiar la contraseña: " . $e->getMessage();
            }
        }
    } 
    // Nuevo bloque para cambiar email
    elseif (isset($_POST['newemail'])) {
        $newEmail = trim($_POST['email']);
        $userId = $_POST['userId'];
        // Validar campos
        if (empty($newEmail) || !filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
            $errorMessage = "Por favor, ingrese un correo electrónico válido.";
        } else {
            try {
                $stmt = $pdo->prepare("UPDATE users SET email = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$newEmail, $userId]);
                
                $successMessage = "Correo electrónico actualizado correctamente.";
                header("Location: users.php");
                exit;
            } catch (PDOException $e) {
                $errorMessage = "Error al cambiar el correo electrónico: " . $e->getMessage();
            }
        }
    } 
}

// Procesar eliminación de usuario
if (isset($_GET['delete'])) {
    $userId = (int)$_GET['delete'];
    try {
        $countStmt = $pdo->query("SELECT COUNT(*) FROM users");
        $count = $countStmt->fetchColumn();
        if ($count < 1) {
            $errorMessage = "No se puede eliminar el último usuario.";
        } else {
            $deleteStmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $deleteStmt->execute([$userId]);
            $successMessage = "Usuario eliminado correctamente.";
        }
        header("Location: users.php");
        exit;
    } catch (PDOException $e) {
        $errorMessage = "Error al eliminar usuario: " . $e->getMessage();
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestor de Usuarios</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .qr-code-container {
            text-align: center;
            margin-bottom: 20px;
        }
        .qr-code-container img {
            border: 1px solid #ddd;
            padding: 5px;
            background-color: #fff;
        }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <h1>Gestión de Usuarios</h1>
        
        <?php if (!empty($successMessage)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($successMessage); ?></div>
        <?php endif; ?>
        
        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($errorMessage); ?></div>
        <?php endif; ?>
        
        <div class="card">
            <h2>Añadir Nuevo Usuario</h2>
            <form method="post">
                <div class="form-group">
                    <label for="username">Usuario:</label>
                    <input type="text" id="username" name="username" required>
                    
                    <label for="password">Contraseña:</label>
                    <input type="password" id="password" name="password" required>

                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <button type="submit" name="add" class="btn">Añadir Usuario</button>
            </form>
        </div>
        
        <div class="card">
            <h2>Lista de Usuarios</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Usuario</th>
                        <th>Email</th>
                        <th>Creado el</th>
                        <th>Actualizado el</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo (int)$user['id']; ?></td>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars($user['created_at']); ?></td>
                        <td><?php echo htmlspecialchars($user['updated_at']); ?></td>
                        <td class="actions">
                            <a class="btn-edit" onclick="openEditPasswordModal(<?php echo (int)$user['id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>')">
                                Cambiar Contraseña
                            </a>
                            <a class="btn-edit-mail" onclick="openEditEmailModal(<?php echo (int)$user['id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>', '<?php echo htmlspecialchars($user['email']); ?>')">
                                Cambiar Email
                            </a>
                            <a class="btn-edit-rol" href="edit_role.php?user_id=<?php echo (int)$user['id']; ?>">
                                Cambiar Rol
                            </a>
                            <a href="users.php?delete=<?php echo (int)$user['id']; ?>" class="btn-delete" 
                               onclick="return confirm('¿Está seguro que desea eliminar este usuario?')">
                                Eliminar
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Modal para cambiar contraseña -->
        <div id="editPasswordModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeModal('editPasswordModal')">&times;</span>
                <h2>Cambiar Contraseña</h2>
                <form method="post">
                    <input type="hidden" id="edit_password_user_id" name="userId">
                    <div class="form-group">
                        <label for="edit_password_username">Usuario:</label>
                        <input type="text" id="edit_password_username" readonly>
                        
                        <label for="new_password">Nueva Contraseña:</label>
                        <input type="password" id="new_password" name="password" required>
                    </div>
                    <button type="submit" name="newpass" class="btn">Actualizar Contraseña</button>
                </form>
            </div>
        </div>

        <!-- Nuevo Modal para cambiar email -->
        <div id="editEmailModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeModal('editEmailModal')">&times;</span>
                <h2>Cambiar Correo Electrónico</h2>
                <form method="post">
                    <input type="hidden" id="edit_email_user_id" name="userId">
                    <div class="form-group">
                        <label for="edit_email_username">Usuario:</label>
                        <input type="text" id="edit_email_username" readonly>
                        
                        <label for="new_email">Nuevo Correo:</label>
                        <input type="email" id="new_email" name="email" required>
                    </div>
                    <button type="submit" name="newemail" class="btn">Actualizar Email</button>
                </form>
            </div>
        </div>
    </div>
    
    <script src="assets/js/script.js"></script>
    <script>
    // Funciones para abrir modal de contraseña
    function openEditPasswordModal(userId, username) {
        document.getElementById('edit_password_user_id').value = userId;
        document.getElementById('edit_password_username').value = username;
        document.getElementById('editPasswordModal').style.display = 'block';
    }
    // Nueva función para abrir modal de email
    function openEditEmailModal(userId, username, email) {
        document.getElementById('edit_email_user_id').value = userId;
        document.getElementById('edit_email_username').value = username;
        document.getElementById('new_email').value = email;
        document.getElementById('editEmailModal').style.display = 'block';
    }
    // Función para cerrar modales
    function closeModal(modalId) {
        document.getElementById(modalId).style.display = 'none';
    }
    // Cerrar modal al hacer clic fuera del contenido
    window.addEventListener('click', function(event) {
        if (event.target == document.getElementById('editPasswordModal')) {
            closeModal('editPasswordModal');
        }
        if (event.target == document.getElementById('editEmailModal')) {
            closeModal('editEmailModal');
        }
    });
    </script>
</body>
</html>
