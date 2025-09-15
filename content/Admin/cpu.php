<?php 
include 'includes/auth.php';
include 'includes/config.php';
checkAuth();

// Inicializar variables
$success = '';
$error = '';

// Procesar eliminación (GET)
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    try {
        // Verificar si hay PCs usando esta CPU
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM pc WHERE cpu_name = ?");
        $stmt->execute([$id]);
        $count = $stmt->fetchColumn();
        
        if ($count > 0) {
            $error = "No se puede eliminar: Hay PCs usando esta CPU";
        } else {
            $stmt = $pdo->prepare("DELETE FROM cpu WHERE id = ?");
            $stmt->execute([$id]);
            $success = "CPU eliminada correctamente";
            // Redirigir para evitar reenvío del formulario
            header("Location: cpu.php");
            exit;
        }
    } catch(PDOException $e) {
        $error = "Error al buscar: " . $e->getMessage();
    }
}
// Si hay búsqueda, filtrar resultados
if (isset($_GET['search'])) {
    $txt = $_GET['search'];
    try {
        $stmt = $pdo->prepare("SELECT * FROM cpu WHERE name LIKE ?");
        $param = "%" . $txt . "%";   // Agregar comodines para búsqueda parcial
        $stmt->execute([$param]);
        $cpus = $stmt->fetchAll(PDO::FETCH_ASSOC);  // Guardar resultado en $cpus
    } catch(PDOException $e) {
        $error = "Error en la búsqueda: " . $e->getMessage();
    }
} else {
    $cpus = $pdo->query("SELECT * FROM cpu ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
}


// Procesar formulario (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Añadir o editar CPU
    if (isset($_POST['add'])) {
        $name = trim($_POST['name']);
        if (!empty($name)) {
            $stmt = $pdo->prepare("INSERT INTO cpu (name) VALUES (?)");
            $stmt->execute([$name]);
            $success = "CPU añadida correctamente";
        } else {
            $error = "El nombre de la CPU no puede estar vacío";
        }
    } elseif (isset($_POST['edit'])) {
        $id = (int)$_POST['id'];
        $name = trim($_POST['name']);
        if (!empty($name)) {
            $stmt = $pdo->prepare("UPDATE cpu SET name = ? WHERE id = ?");
            $stmt->execute([$name, $id]);
            $success = "CPU actualizada correctamente";
        } else {
            $error = "El nombre de la CPU no puede estar vacío";
        }
    } elseif (isset($_POST['search'])){
        $txt = $_POST['pattern'];
        header("Location: cpu.php?search=$txt");
        exit;
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de CPUs</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <h1>Gestión de CPUs</h1>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        
        <!-- Carta añadir nueva CPU -->
        <div class="card">
            <h2>Añadir Nueva CPU</h2>
            <form method="post">
                <div class="form-group" style="display: flex; gap: 10px; justify-content: space-around; width: 100%;">
                    <input type="text" id="name" name="name" required placeholder="Nombre de la CPU">
                    <button type="submit" name="add" class="btn" style="width: 150px;">Añadir CPU</button>
                </div>
                
            </form>
        </div>
        <!-- Listado -->
        <div class="card">
            <!-- Buscador -->
            <form method="post">
                <div class="form-group" style="display: inline-flex; gap: 10px; justify-content: space-around; width: 100%;">
                    <input type="text" id="pattern" name="pattern" placeholder="Buscar..." autofocus>
                    <button type="submit" name="search" class="btn">Buscar</button>
                </div>
            </form>

            <h2>Lista de CPUs</h2>
            <?php if (empty($cpus)): ?>
                <p>No hay CPUs registradas</p>
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
                        <?php foreach ($cpus as $cpu): ?>
                        <tr>
                            <td><?= $cpu['id'] ?></td>
                            <td><?= htmlspecialchars($cpu['name']) ?></td>
                            <td>
                                <a href="#" class="btn-edit" onclick="editCPU(<?= $cpu['id'] ?>, '<?= htmlspecialchars($cpu['name'], ENT_QUOTES) ?>')">Editar</a>
                                <a href="cpu.php?delete=<?= $cpu['id'] ?>" class="btn-delete" onclick="return confirm('¿Estás seguro de eliminar esta CPU?')">Eliminar</a>
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
                <h2>Editar CPU</h2>
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
    // Función para abrir modal y cargar datos
    function editCPU(id, name) {
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_name').value = name;
        document.getElementById('editModal').style.display = 'block';
    }
    
    // Cerrar modal
    document.querySelector('.close').addEventListener('click', function() {
        document.getElementById('editModal').style.display = 'none';
    });
    // Cerrar modal al hacer clic fuera del contenido
    window.addEventListener('click', function(event) {
        if (event.target == document.getElementById('editModal')) {
            document.getElementById('editModal').style.display = 'none';
        }
    });
    </script>
</body>
</html>