<?php 
// Incluir archivos necesarios
require 'includes/auth.php';
require 'includes/config.php';

// Verificar autenticación
checkAuth();

// Variables para mensajes
$successMessage = '';
$errorMessage = '';

// Obtener datos para selects
try {
    $cpuQuery = $pdo->query("SELECT * FROM cpu ORDER BY name");
    $ramQuery = $pdo->query("SELECT * FROM ram ORDER BY capacity");
    $discQuery = $pdo->query("SELECT * FROM disc ORDER BY capacity");
    $gpuQuery = $pdo->query("SELECT * FROM gpu ORDER BY name");
    
    $cpus = $cpuQuery->fetchAll(PDO::FETCH_ASSOC);
    $rams = $ramQuery->fetchAll(PDO::FETCH_ASSOC);
    $discs = $discQuery->fetchAll(PDO::FETCH_ASSOC);
    $gpus = $gpuQuery->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $errorMessage = "Error al cargar componentes: " . $e->getMessage();
}

// Procesar formulario de añadir PC
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add'])) {
    try {
        $insertStmt = $pdo->prepare("INSERT INTO pc (
            board_type, cpu_name, ram_capacity, ram_type, 
            disc_capacity, disc_type, gpu_name, gpu_type, 
            wifi, bluetooth, obser
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $insertStmt->execute([
            $_POST['board_type'],
            $_POST['cpu_name'],
            $_POST['ram_capacity'],
            $_POST['ram_type'],
            $_POST['disc_capacity'],
            $_POST['disc_type'],
            $_POST['gpu_name'] ?: null,
            $_POST['gpu_type'],
            $_POST['wifi'],
            $_POST['bluetooth'],
            $_POST['obser']
        ]);
        
        $successMessage = "PC añadida correctamente";
        // Redirigir para evitar reenvío del formulario
        header("Location: pc.php");
        exit;
    } catch(PDOException $e) {
        $errorMessage = "Error al añadir PC: " . $e->getMessage();
    }
}

// Procesar eliminación de PC
if (isset($_GET['delete'])) {
    $pcId = (int)$_GET['delete'];
    try {
        $deleteStmt = $pdo->prepare("DELETE FROM pc WHERE id = ?");
        $deleteStmt->execute([$pcId]);
        $successMessage = "PC eliminada correctamente";
        header("Location: pc.php");
        exit;
    } catch(PDOException $e) {
        $errorMessage = "Error al eliminar PC: " . $e->getMessage();
    }
}

// Obtener todas las PCs con información de componentes
try {
    $pcsQuery = $pdo->query("
        SELECT pc.*, 
               cpu.name as cpu_name_text, 
               ram.capacity as ram_capacity_text, 
               disc.capacity as disc_capacity_text, 
               gpu.name as gpu_name_text
        FROM pc
        LEFT JOIN cpu ON pc.cpu_name = cpu.id
        LEFT JOIN ram ON pc.ram_capacity = ram.id
        LEFT JOIN disc ON pc.disc_capacity = disc.id
        LEFT JOIN gpu ON pc.gpu_name = gpu.id
        ORDER BY pc.id
    ");
    $pcs = $pcsQuery->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    $errorMessage = "Error al cargar PCs: " . $e->getMessage();
    $pcs = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de PCs</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <h1>Gestión de PCs</h1>
        
        <!-- Mostrar mensajes -->
        <?php if (!empty($successMessage)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($successMessage); ?></div>
        <?php endif; ?>
        
        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($errorMessage); ?></div>
        <?php endif; ?>
    
        
        <!-- Listado de PCs -->
        <div class="card">
            <h2>Lista de PCs</h2>
            <?php if (empty($pcs)): ?>
                <p>No hay PCs registradas</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>SN</th>
                                <th>Placa</th>
                                <th>Procesador</th>
                                <th>RAM</th>
                                <th>Disco</th>
                                <th>Gráfica</th>
                                <th>Conectividad</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pcs as $pc): ?>
                            <tr>
                                <td><?php echo (int)$pc['id']; ?></td>
                                <td><?php 
                                    $stmt = $pdo->prepare("SELECT sn.prefix as prefix, sn.num as num FROM sn JOIN sn_pc ON sn.id = sn_pc.sn_id WHERE sn_pc.pc_id = :pc_id;");
                                    $stmt->bindParam(':pc_id', $pc['id'], PDO::PARAM_INT);
                                    $stmt->execute();

                                    $result = $stmt->fetch(PDO::FETCH_ASSOC);
                                    echo htmlspecialchars($result['prefix'] . "-" . str_pad($result['num'], 4, 0, STR_PAD_LEFT))

                                ?></td>
                                <td><?php echo htmlspecialchars(strtoupper($pc['board_type'])); ?></td>
                                <td><?php echo htmlspecialchars($pc['cpu_name_text']); ?></td>
                                <td>
                                    <?php 
                                    echo $pc['ram_capacity_text'] ? (int)$pc['ram_capacity_text'] . 'GB ' : '';
                                    echo htmlspecialchars($pc['ram_type']); 
                                    ?>
                                </td>
                                <td>
                                    <?php 
                                    echo $pc['disc_capacity_text'] ? (int)$pc['disc_capacity_text'] . 'GB ' : '';
                                    echo htmlspecialchars($pc['disc_type']); 
                                    ?>
                                </td>
                                <td>
                                    <?php if ($pc['gpu_name_text']): ?>
                                        <?php echo htmlspecialchars($pc['gpu_name_text']); ?>
                                        (<?php echo htmlspecialchars($pc['gpu_type']); ?>)
                                    <?php else: ?>
                                        Integrada
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    if ($pc['wifi']=="false"){
                                        $wifi = "<input type='checkbox' disabled>";
                                    } else {
                                        $wifi = "<input type='checkbox' checked disabled>";
                                    }

                                    if ($pc['bluetooth']=="false") {
                                        $bluetooth = "<input type='checkbox' disabled>";
                                        
                                    } else {
                                        $bluetooth = "<input type='checkbox' checked disabled>";
                                    }
                                    ?>
                                    WiFi: <?php echo $wifi;?><br>
                                    Bluetooth: <?php echo $bluetooth; ?>
                                </td>
                                <td class="actions">
                                    <a href="edit_pc.php?id=<?php echo (int)$pc['id']; ?>" class="btn-edit">
                                        Editar
                                    </a>
                                    <a href="pc.php?delete=<?php echo (int)$pc['id']; ?>" class="btn-delete" 
                                       onclick="return confirm('¿Está seguro que desea eliminar esta PC?')">
                                        Eliminar
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="assets/js/script.js"></script>
    <script>
    // Validación del formulario
    document.getElementById('pcForm').addEventListener('submit', function(e) {
        // Validaciones adicionales pueden ir aquí
        return true;
    });
    </script>
</body>
</html>