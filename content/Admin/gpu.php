<?php 
// Incluir archivos necesarios
require 'includes/auth.php';
require 'includes/config.php';

// Verificar autenticación
checkAuth();

// Variables para mensajes
$successMessage = '';
$errorMessage = '';

// Procesar eliminación de GPU (GET)
if (isset($_GET['delete'])) {
    $gpuId = (int)$_GET['delete'];
    try {
        // Verificar si hay PCs usando esta GPU
        $checkPcStmt = $pdo->prepare("SELECT COUNT(*) FROM pc WHERE gpu_name = ?");
        $checkPcStmt->execute([$gpuId]);
        $pcCount = $checkPcStmt->fetchColumn();
        
        if ($pcCount > 0) {
            $errorMessage = "No se puede eliminar: Hay PCs que utilizan esta GPU";
        } else {
            $deleteStmt = $pdo->prepare("DELETE FROM gpu WHERE id = ?");
            $deleteStmt->execute([$gpuId]);
            $successMessage = "GPU eliminada correctamente";
            header("Location: gpu.php");
            exit;
        }
    } catch(PDOException $e) {
        $errorMessage = "Error al eliminar GPU: " . $e->getMessage();
    }
}

if (isset($_GET['search'])) {
    $txt = $_GET['search'];
    try {
        $stmt = $pdo->prepare("SELECT * FROM gpu WHERE name LIKE ?");
        $param = "%" . $txt . "%";   // Agregar comodines para búsqueda parcial
        $stmt->execute([$param]);
        $gpus = $stmt->fetchAll(PDO::FETCH_ASSOC);  // Guardar resultado en $cpus
    } catch(PDOException $e) {
        $error = "Error en la búsqueda: " . $e->getMessage();
    }
} else {
    $gpus = $pdo->query("SELECT * FROM gpu ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
}

// Procesar formularios (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Añadir nueva GPU
    if (isset($_POST['add'])) {
        $gpuName = trim($_POST['name']);
        
        if (empty($gpuName)) {
            $errorMessage = "El nombre de la GPU no puede estar vacío";
        } else {
            try {
                $insertStmt = $pdo->prepare("INSERT INTO gpu (name) VALUES (?)");
                $insertStmt->execute([$gpuName]);
                $successMessage = "GPU añadida correctamente";
                // Limpiar el campo del formulario
                $_POST['name'] = '';
            } catch(PDOException $e) {
                $errorMessage = "Error al añadir GPU: " . $e->getMessage();
            }
        }
    } 
    // Editar GPU existente
    elseif (isset($_POST['edit'])) {
        $gpuId = (int)$_POST['id'];
        $gpuName = trim($_POST['name']);
        
        if (empty($gpuName)) {
            $errorMessage = "El nombre de la GPU no puede estar vacío";
        } else {
            try {
                $updateStmt = $pdo->prepare("UPDATE gpu SET name = ? WHERE id = ?");
                $updateStmt->execute([$gpuName, $gpuId]);
                $successMessage = "GPU actualizada correctamente";
                header("Location: gpu.php");
                exit;
            } catch(PDOException $e) {
                $errorMessage = "Error al actualizar GPU: " . $e->getMessage();
            }
        }
    } elseif (isset($_POST['search'])){
        $txt = $_POST['pattern'];
        header("Location: gpu.php?search=$txt");
        exit;
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestor de GPUs</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <h1>Gestión de GPUs</h1>
        
        <!-- Mostrar mensajes -->
        <?php if (!empty($successMessage)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($successMessage); ?></div>
        <?php endif; ?>
        
        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($errorMessage); ?></div>
        <?php endif; ?>
        
        <!-- Formulario para añadir GPU -->
        <div class="card">
            <h2>Añadir Nueva GPU</h2>
            <form method="post">
                <div class="form-group" style="display: inline-flex; gap: 10px; justify-content: space-around; width: 100%;">
                    <input type="text" id="name" name="name" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required placeholder="Nombre de la GPU">
                    <button type="submit" name="add" class="btn" style="width: 150px;">Añadir GPU</button>
                </div>
            </form>
        </div>
        

        <!-- Listado de GPUs -->
        <div class="card">
            <!-- Buscador -->
            <form method="post">
                <div class="form-group" style="display: inline-flex; gap: 10px; justify-content: space-around; width: 100%;">
                    <input type="text" id="pattern" name="pattern" placeholder="Buscar..." autofocus>
                    <button type="submit" name="search" class="btn">Buscar</button>
                </div>
            </form>

            <h2>Lista de GPUs</h2>
            <?php if (empty($gpus)): ?>
                <p>No hay GPUs registradas</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($gpus as $gpu): ?>
                        <tr>
                            <td><?php echo (int)$gpu['id']; ?></td>
                            <td><?php echo htmlspecialchars($gpu['name']); ?></td>
                            <td class="actions">
                                <a class="btn-edit" onclick="openEditModal(<?php echo (int)$gpu['id']; ?>, '<?php echo htmlspecialchars($gpu['name']); ?>')">
                                    Editar
                                </a>
                                <a href="gpu.php?delete=<?php echo (int)$gpu['id']; ?>" class="btn-delete" 
                                   onclick="return confirm('¿Está seguro que desea eliminar esta GPU?')">
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
                <h2>Editar GPU</h2>
                <form method="post">
                    <input type="hidden" id="edit_id" name="id">
                    <div class="form-group">
                        <label for="edit_name">Nombre:</label>
                        <input type="text" id="edit_name" name="name" required>
                    </div>
                    <button type="submit" name="edit" class="btn">Guardar Cambios</button>
                </form>
            </div>
        </div>
    </div>
    
    <script src="assets/js/script.js"></script>
    <script>
    // Función para abrir el modal de edición
    function openEditModal(id, name) {
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_name').value = name;
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