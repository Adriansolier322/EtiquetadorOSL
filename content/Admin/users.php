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

// Procesar formulario de añadir usuario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add'])) {
        $newUsername = trim($_POST['username']);
        $newPassword = $_POST['password'];
        
        // Validar campos
        if (empty($newUsername) || empty($newPassword)) {
            $errorMessage = "Por favor complete todos los campos";
        } else {
            try {
                // Hash de la contraseña
                $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
                
                // Insertar nuevo usuario
                $insertStmt = $pdo->prepare("INSERT INTO users (username, password, role_id) VALUES (?, ?, ?)");
                $insertStmt->execute([$newUsername, $passwordHash, 2]); // Asignar rol de usuario por defecto

                $successMessage = "Usuario añadido correctamente";
                // Recargar la lista de usuarios
                header("Refresh:0");
                exit;
            } catch (PDOException $e) {
                $errorMessage = "Error al añadir usuario: " . $e->getMessage();
            }
        }
    } elseif (isset($_POST['newpass'])) {
        $newpass = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $userId = $_POST['userId'];

        if (empty($newpass)) {
            $errorMessage = "Por favor complete todos los campos";
        } else {
            try {
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$newpass,$userId]);
                header("Location: users.php");
                exit;

            } catch (PDOException $e) {
                $errorMessage = "Error al cambiar la contraseña";
            }
        }
    }
}

// Procesar eliminación de usuario
if (isset($_GET['delete'])) {
    $userId = (int)$_GET['delete'];
    try {
        $count = $pdo->prepare("SELECT COUNT(*) FROM users")->execute();
        if ($count<1) {
            exit;
        }

        $deleteStmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $deleteStmt->execute([$userId]);
        $successMessage = "Usuario eliminado correctamente";
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
                        <td><?php echo htmlspecialchars($user['created_at']); ?></td>
                        <td><?php echo htmlspecialchars($user['updated_at']); ?></td>
                        <td class="actions">
                            <a class="btn-edit" onclick="openEditModal(<?php echo (int)$user['id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>')">
                                Cambiar Contraseña
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
        <div id="editModal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h2>Cambiar Contraseña</h2>
                <form method="post">
                    <input type="hidden" id="edit_user_id" name="userId">
                    <div class="form-group">
                        <label for="edit_username">Usuario:</label>
                        <input type="text" id="edit_username" readonly>
                        
                        <label for="new_password">Nueva Contraseña:</label>
                        <input type="password" id="new_password" name="password" required>
                    </div>
                    <button type="submit" name="newpass" class="btn">Actualizar Contraseña</button>
                </form>
            </div>
        </div>
    </div>
    
    <script src="assets/js/script.js"></script>
    <script>
    // Función para abrir el modal de edición
    function openEditModal(userId, username) {
        document.getElementById('edit_user_id').value = userId;
        document.getElementById('edit_username').value = username;
        document.getElementById('editModal').style.display = 'block';
    }
    
    // Cerrar modal al hacer clic en la X
    document.querySelector('.modal .close').addEventListener('click', function() {
        document.getElementById('editModal').style.display = 'none';
    });
    
    // Cerrar modal al hacer clic fuera
    window.addEventListener('click', function(event) {
        if (event.target == document.getElementById('editModal')) {
            document.getElementById('editModal').style.display = 'none';
        }
    });
    </script>
</body>
</html>