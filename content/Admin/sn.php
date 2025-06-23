<?php 
// Incluir archivos necesarios
require 'includes/auth.php';
require 'includes/config.php';

// Verificar autenticación
checkAuth();

// Variables para mensajes
$successMessage = '';
$errorMessage = '';

// Procesar formulario de añadir prefijo SN
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add'])) {
    $prefix = trim($_POST['prefix']);
    
    if (empty($prefix)) {
        $errorMessage = "El prefijo no puede estar vacío";
    } else {
        try {
            $insertStmt = $pdo->prepare("INSERT INTO sn (prefix, num) VALUES (?, ?)");
            $insertStmt->execute([$prefix, 0]);
            $successMessage = "Prefijo SN añadido correctamente";
            header("Location: sn.php");
            exit;
        } catch(PDOException $e) {
            $errorMessage = "Error al añadir prefijo SN: " . $e->getMessage();
        }
    }
}

// Procesar eliminación de prefijo SN
if (isset($_GET['delete'])) {
    $snId = (int)$_GET['delete'];
    try {
        $deleteStmt = $pdo->prepare("DELETE FROM sn WHERE id = ?");
        $deleteStmt->execute([$snId]);
        $successMessage = "Prefijo SN eliminado correctamente";
        header("Location: sn.php");
        exit;
    } catch(PDOException $e) {
        $errorMessage = "Error al eliminar prefijo SN: " . $e->getMessage();
    }
}

// Obtener todos los prefijos SN
try {
    $snsQuery = $pdo->query("SELECT * FROM sn ORDER BY prefix, num");
    $sns = $snsQuery->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $errorMessage = "Error al cargar prefijos SN: " . $e->getMessage();
    $sns = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Números de Serie</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <h1>Gestión de Números de Serie</h1>
        
        <!-- Mostrar mensajes -->
        <?php if (!empty($successMessage)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($successMessage); ?></div>
        <?php endif; ?>
        
        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($errorMessage); ?></div>
        <?php endif; ?>
        
        <!-- Formulario para añadir prefijo SN -->
        <div class="card">
            <h2>Añadir Nuevo Prefijo SN</h2>
            <form method="post">
                <div class="form-group" style="display: inline-flex; gap: 10px; justify-content: space-around; width: 100%;">
                    <input type="text" id="prefix" name="prefix" required 
                           pattern="[A-Z]{3}" title="3 caracteres"
                           placeholder="Ej: PRU" maxlength="3" minlength="3" style="text-transform: uppercase;">
                    <button type="submit" name="add" class="btn" style="width: 150px;">Añadir Prefijo</button>
                </div>
                
            </form>
        </div>
        
        <!-- Listado de prefijos SN -->
        <div class="card">
            <h2>Lista de Prefijos SN</h2>
            <?php if (empty($sns)): ?>
                <p>No hay prefijos SN registrados</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Prefijo</th>
                            <th>Último Número</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sns as $sn): ?>
                        <tr>
                            <td><?php echo (int)$sn['id']; ?></td>
                            <td><?php echo htmlspecialchars($sn['prefix']); ?></td>
                            <td><?php echo (int)$sn['num']; ?></td>
                            <td class="actions">
                                <a class="btn-edit" onclick="openEditModal(
                                    <?php echo (int)$sn['id']; ?>, 
                                    '<?php echo htmlspecialchars($sn['prefix']); ?>'
                                )">
                                    Editar
                                </a>
                                <a href="sn.php?delete=<?php echo (int)$sn['id']; ?>" class="btn-delete" 
                                   onclick="return confirm('¿Está seguro que desea eliminar este prefijo SN?')">
                                    Eliminar
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        
        <!-- Modal para editar -->
        <div id="editModal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h2>Editar Prefijo SN</h2>
                <form method="post" action="update_sn.php">
                    <input type="hidden" id="edit_id" name="id">
                    <div class="form-group">
                        <label for="edit_prefix">Prefijo:</label>
                        <input type="text" id="edit_prefix" name="prefix" required
                               pattern="[A-Za-z0-9]{1,10}" title="Máximo 10 caracteres alfanuméricos">
                    </div>
                    <button type="submit" name="edit" class="btn">Guardar Cambios</button>
                </form>
            </div>
        </div>
    </div>
    
    <script src="assets/js/script.js"></script>
    <script>
    // Función para abrir el modal de edición
    function openEditModal(id, prefix) {
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_prefix').value = prefix;
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