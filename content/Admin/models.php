<?php 
include 'includes/auth.php';
checkAuth();
include 'includes/config.php';

// Inicializar variables
$success = '';
$error = '';


// Obtener todas las Modelos
$models = $pdo->query("SELECT * FROM models ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Modelos</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <h1>Gestión de Modelos</h1>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        
        <div class="card">
            <h2>Añadir Nueva Modelo</h2>
            <form method="post">
                <div class="form-group">
                    <label for="name">Nombre de la Modelo:</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <button type="submit" name="add" class="btn">Añadir Modelo</button>
            </form>
        </div>
        
        <div class="card">
            <h2>Lista de Modelos</h2>
            <?php if (empty($models)): ?>
                <p>No hay Modelos registradas</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Especificaciones</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($models as $model): ?>
                        <tr>
                            <td><?= $model['id'] ?></td>
                            <td><?= htmlspecialchars($model['name']) ?></td>
                            <td><?= htmlspecialchars($model['model']) ?></td>
                            <td>
                                <a href="#" class="btn-edit" onclick="editCPU(<?= $model['id'] ?>, '<?= htmlspecialchars($model['name'], ENT_QUOTES) ?>')">Editar</a>
                                <a href="model.php?delete=<?= $model['id'] ?>" class="btn-delete" onclick="return confirm('¿Estás seguro de eliminar estes Modelo?')">Eliminar</a>
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
                <h2>Editar Modelo</h2>
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
    
    <script>
    function editCPU(id, name) {
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_name').value = name;
        document.getElementById('editModal').style.display = 'block';
    }
    
    // Cerrar modal
    document.querySelector('.close').addEventListener('click', function() {
        document.getElementById('editModal').style.display = 'none';
    });
    
    window.addEventListener('click', function(event) {
        if (event.target == document.getElementById('editModal')) {
            document.getElementById('editModal').style.display = 'none';
        }
    });
    </script>
</body>
</html>