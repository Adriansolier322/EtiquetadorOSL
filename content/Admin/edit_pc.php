<?php
// Enable error reporting for development (remove or restrict in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include your database connection (PDO)
// Make sure this file correctly establishes your $pdo connection
require_once 'includes/config.php';

$pcData = null; // Initialize to null
$errorMessage = "";
$successMessage = "";

/**
 * Fetches distinct values (ID and Name/Capacity) from a lookup table.
 * Used for CPU, RAM, Disc, GPU dropdowns.
 *
 * @param PDO $pdo The PDO database connection object.
 * @param string $tableName The name of the lookup table (e.g., 'cpu', 'ram').
 * @param string $idColumn The name of the ID column (always 'id').
 * @param string $valueColumn The name of the value column (e.g., 'name', 'capacity').
 * @return array An array of associative arrays, each containing 'id' and 'value'.
 */
function fetchDistinctComponentValues($pdo, $tableName, $idColumn, $valueColumn) {
    try {
        // For numerical sorting (like capacity), add 0 to ensure proper numeric comparison
        $orderByClause = ($valueColumn === 'capacity') ? "ORDER BY $valueColumn + 0" : "ORDER BY $valueColumn";
        $stmt = $pdo->prepare("SELECT $idColumn, $valueColumn FROM $tableName WHERE $valueColumn IS NOT NULL AND $valueColumn != '' $orderByClause");
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Reformat for consistency: 'id' and 'value'
        return array_map(function($item) use ($idColumn, $valueColumn) {
            return ['id' => $item[$idColumn], 'value' => $item[$valueColumn]];
        }, $results);

    } catch (PDOException $e) {
        error_log("Error fetching distinct values for $tableName.$valueColumn: " . $e->getMessage());
        return [];
    }
}

/**
 * Fetches distinct string values directly from the 'pc' table.
 * Used for ram_type, disc_type dropdowns (board_type and gpu_type are now hardcoded/checkboxes).
 *
 * @param PDO $pdo The PDO database connection object.
 * @param string $columnName The column name from the 'pc' table.
 * @return array A simple array of distinct string values (e.g., ['ATX', 'Micro-ATX']).
 */
function fetchDistinctPcFieldValues($pdo, $columnName) {
    try {
        $stmt = $pdo->prepare("SELECT DISTINCT " . $columnName . " FROM pc WHERE " . $columnName . " IS NOT NULL AND " . $columnName . " != '' ORDER BY " . $columnName);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN); // Fetch as a simple indexed array of strings
    } catch (PDOException $e) {
        error_log("Error fetching distinct values for pc.$columnName: " . $e->getMessage());
        return [];
    }
}

/**
 * Checks if a component value exists in a lookup table. If not, inserts it and returns its ID.
 *
 * @param PDO $pdo The PDO database connection object.
 * @param string $tableName The name of the lookup table (e.g., 'cpu', 'ram').
 * @param string $columnName The name of the value column (e.g., 'name', 'capacity').
 * @param string $value The value to check/insert.
 * @return int The ID of the existing or newly inserted component.
 * @throws PDOException If there's a database error.
 */
function getOrCreateComponentId($pdo, $tableName, $columnName, $value) {
    // Check if value exists
    $stmt = $pdo->prepare("SELECT id FROM $tableName WHERE $columnName = ?");
    $stmt->execute([$value]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        return (int)$result['id'];
    } else {
        // Insert new value
        $stmt = $pdo->prepare("INSERT INTO $tableName ($columnName) VALUES (?)");
        $stmt->execute([$value]);
        return (int)$pdo->lastInsertId();
    }
}


// --- Get options from database for all relevant fields ---
// Component lookup tables (ID and Value)
$dbCpuNames = fetchDistinctComponentValues($pdo, 'cpu', 'id', 'name');
$dbRamCapacities = fetchDistinctComponentValues($pdo, 'ram', 'id', 'capacity');
$dbDiscCapacities = fetchDistinctComponentValues($pdo, 'disc', 'id', 'capacity');
$dbGpuNames = fetchDistinctComponentValues($pdo, 'gpu', 'id', 'name');

// Direct PC table fields (string values)
// Board Type: Hardcoded options, 'Other' removed
$boardTypesRaw = ['UEFI', 'BIOS']; // Hardcoded values
sort($boardTypesRaw);
$boardTypes = array_map(function($val) { return ['value' => $val]; }, $boardTypesRaw);

// RAM Types: Hardcoded options + fetched from DB, sorted, then 'Other'
$ramTypesRaw = array_unique(array_merge(['DDR2','DDR3', 'DDR4', 'DDR5'], fetchDistinctPcFieldValues($pdo, 'ram_type')));
sort($ramTypesRaw);
$ramTypes = array_map(function($val) { return ['value' => $val]; }, $ramTypesRaw);

// Disc Types: Hardcoded options
$discTypesRaw = ['NVMe', 'HDD', 'SSD']; // Hardcoded values
sort($discTypesRaw);
$discTypes = array_map(function($val) { return ['value' => $val]; }, $discTypesRaw);


// For component tables, we need to preserve both ID and value for 'Other' handling
// Add a special '_OTHER_' ID to indicate a new value will be entered
$cpuNames = array_merge($dbCpuNames, [['id' => '_OTHER_', 'value' => 'Other']]);
$ramCapacities = array_merge($dbRamCapacities, [['id' => '_OTHER_', 'value' => 'Other']]);
$discCapacities = array_merge($dbDiscCapacities, [['id' => '_OTHER_', 'value' => 'Other']]);
$gpuNames = array_merge($dbGpuNames, [['id' => '_OTHER_', 'value' => 'Other']]);


// --- Handle Form Submission (POST Request) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_edit_pc'])) {
    $pcId = filter_input(INPUT_POST, 'pc_id', FILTER_SANITIZE_NUMBER_INT);

    // Handle direct string fields from POST (Board Type, RAM Type, Disc Type)
    $boardType = trim(filter_input(INPUT_POST, 'board_type', FILTER_UNSAFE_RAW));
    $ramType = trim(filter_input(INPUT_POST, 'ram_type', FILTER_UNSAFE_RAW));
    $discType = trim(filter_input(INPUT_POST, 'disc_type', FILTER_UNSAFE_RAW));

    // Handle GPU Type radio button
    $gpuType = $_POST['gpu_type_radio'] ?? '';
    $gpuType = trim(filter_var($gpuType, FILTER_UNSAFE_RAW));


    $wifi = isset($_POST['wifi']) ? 'true' : 'false';
    $bluetooth = isset($_POST['bluetooth']) ? 'true' : 'false';
    $obser = trim(filter_input(INPUT_POST, 'obser', FILTER_UNSAFE_RAW));


    // Handle component IDs (CPU, RAM, Disc, GPU)
    try {
        // CPU Name
        $cpuNameId = null;
        $submittedCpuName = $_POST['cpu_name'] ?? '';
        if ($submittedCpuName === '_OTHER_') {
            $newCpuName = trim(filter_input(INPUT_POST, 'cpu_name_other', FILTER_UNSAFE_RAW));
            if (!empty($newCpuName)) {
                $cpuNameId = getOrCreateComponentId($pdo, 'cpu', 'name', $newCpuName);
            } else {
                $errorMessage = "El nombre del procesador 'Otro' no puede estar vacío.";
            }
        } elseif (!empty($submittedCpuName)) {
            $cpuNameId = (int)$submittedCpuName; // It's an existing ID
        }

        // RAM Capacity
        $ramCapacityId = null;
        $submittedRamCapacity = $_POST['ram_capacity'] ?? '';
        if ($submittedRamCapacity === '_OTHER_') {
            $newRamCapacity = trim(filter_input(INPUT_POST, 'ram_capacity_other', FILTER_UNSAFE_RAW));
            if (!empty($newRamCapacity)) {
                $ramCapacityId = getOrCreateComponentId($pdo, 'ram', 'capacity', $newRamCapacity);
            } else {
                $errorMessage = "La capacidad de RAM 'Otra' no puede estar vacía.";
            }
        } elseif (!empty($submittedRamCapacity)) {
            $ramCapacityId = (int)$submittedRamCapacity;
        }

        // Disc Capacity
        $discCapacityId = null;
        $submittedDiscCapacity = $_POST['disc_capacity'] ?? '';
        if ($submittedDiscCapacity === '_OTHER_') {
            $newDiscCapacity = trim(filter_input(INPUT_POST, 'disc_capacity_other', FILTER_UNSAFE_RAW));
            if (!empty($newDiscCapacity)) {
                $discCapacityId = getOrCreateComponentId($pdo, 'disc', 'capacity', $newDiscCapacity);
            } else {
                $errorMessage = "La capacidad de disco 'Otra' no puede estar vacía.";
            }
        } elseif (!empty($submittedDiscCapacity)) {
            $discCapacityId = (int)$submittedDiscCapacity;
        }

        // GPU Name (Optional field, can be empty)
        $gpuNameId = null;
        $submittedGpuName = $_POST['gpu_name'] ?? '';
        if ($submittedGpuName === '_OTHER_') {
            $newGpuName = trim(filter_input(INPUT_POST, 'gpu_name_other', FILTER_UNSAFE_RAW));
            if (!empty($newGpuName)) {
                $gpuNameId = getOrCreateComponentId($pdo, 'gpu', 'name', $newGpuName);
            }
        } elseif (!empty($submittedGpuName)) {
            $gpuNameId = (int)$submittedGpuName;
        }


        // Basic server-side validation for required fields
        if (empty($pcId) || empty($boardType) || empty($cpuNameId) || empty($ramCapacityId) || empty($ramType) || empty($discCapacityId) || empty($discType) || !empty($errorMessage)) {
            $errorMessage = $errorMessage ?: "Todos los campos obligatorios deben ser llenados.";
        } else {
            // Update query with actual table column names (which are foreign keys now)
            $updateQuery = $pdo->prepare("UPDATE pc SET
                board_type = ?,
                cpu_name = ?,
                ram_capacity = ?,
                ram_type = ?,
                disc_capacity = ?,
                disc_type = ?,
                gpu_name = ?,
                gpu_type = ?,
                wifi = ?,
                bluetooth = ?,
                obser = ?
                WHERE id = ?");

            $updateQuery->execute([
                $boardType,
                $cpuNameId,
                $ramCapacityId,
                $ramType,
                $discCapacityId,
                $discType,
                $gpuNameId,
                $gpuType,
                $wifi,
                $bluetooth,
                $obser,
                $pcId
            ]);

            if ($updateQuery->rowCount() > 0) {
                // Redirect to pc.php after successful update
                header('Location: pc.php');
                exit(); // Important to stop further script execution
            } else {
                $errorMessage = "No se realizaron cambios o la PC no fue encontrada.";
            }

        }
    } catch (PDOException $e) {
        $errorMessage = "Error al actualizar la PC: " . $e->getMessage();
    }
}

// --- Load PC Data for Display (GET Request or after POST if not redirecting) ---
if (isset($_GET['id']) && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $pcId = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
    if ($pcId) {
        try {
            // Select query with JOINs to get actual names/capacities
            $pcQuery = $pdo->prepare("
                SELECT
                    p.id, p.board_type, p.ram_type, p.disc_type, p.gpu_type, p.wifi, p.bluetooth, p.obser,
                    c.name AS cpu_name,
                    r.capacity AS ram_capacity,
                    d.capacity AS disc_capacity,
                    g.name AS gpu_name
                FROM pc p
                LEFT JOIN cpu c ON p.cpu_name = c.id
                LEFT JOIN ram r ON p.ram_capacity = r.id
                LEFT JOIN disc d ON p.disc_capacity = d.id
                LEFT JOIN gpu g ON p.gpu_name = g.id
                WHERE p.id = ?
            ");
            $pcQuery->execute([$pcId]);
            $pcData = $pcQuery->fetch(PDO::FETCH_ASSOC);

            if (!$pcData) {
                $errorMessage = "PC no encontrada.";
            }
        } catch(PDOException $e) {
            $errorMessage = "Error al cargar PC: " . $e->getMessage();
        }
    } else {
        $errorMessage = "ID de PC no válido.";
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_edit_pc']) && !$pcData) {
    // If it was a POST request that failed, we still need to load the data
    // to repopulate the form, using the pc_id from the POST data.
    $pcId = filter_input(INPUT_POST, 'pc_id', FILTER_SANITIZE_NUMBER_INT);
    if ($pcId) {
        try {
            $pcQuery = $pdo->prepare("
                SELECT
                    p.id, p.board_type, p.ram_type, p.disc_type, p.gpu_type, p.wifi, p.bluetooth, p.obser,
                    c.name AS cpu_name,
                    r.capacity AS ram_capacity,
                    d.capacity AS disc_capacity,
                    g.name AS gpu_name
                FROM pc p
                LEFT JOIN cpu c ON p.cpu_name = c.id
                LEFT JOIN ram r ON p.ram_capacity = r.id
                LEFT JOIN disc d ON p.disc_capacity = d.id
                LEFT JOIN gpu g ON p.gpu_name = g.id
                WHERE p.id = ?
            ");
            $pcQuery->execute([$pcId]);
            $pcData = $pcQuery->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            $errorMessage .= " Error al re-cargar datos para el formulario: " . $e->getMessage();
        }
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar PC</title>
    <style>
        body { font-family: sans-serif; margin: 20px; background-color: #f4f7f6; color: #333; }
        .message { padding: 10px; margin-bottom: 15px; border-radius: 5px; font-weight: bold; }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        h1 { color: #2c3e50; text-align: center; margin-bottom: 30px; }
        form {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            max-width: 600px;
            margin: 0 auto;
        }
        label { display: block; margin-bottom: 8px; font-weight: bold; color: #555; }
        input[type="text"], select, textarea {
            width: calc(100% - 22px);
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 6px;
            box-sizing: border-box;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }
        input[type="text"]:focus, select:focus, textarea:focus {
            border-color: #007bff;
            outline: none;
        }
        .checkbox-group {
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }
        .checkbox-group input[type="checkbox"] {
            margin-right: 10px;
            width: auto; /* Override default input width */
        }
        .checkbox-group label {
            margin-bottom: 0;
            display: inline-block;
        }
        input[type="submit"] {
            background-color: #007bff;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 18px;
            font-weight: bold;
            transition: background-color 0.3s ease, transform 0.2s ease;
            display: block;
            width: 100%;
            box-sizing: border-box;
        }
        input[type="submit"]:hover {
            background-color: #0056b3;
            transform: translateY(-2px);
        }
        .other-input {
            display: none; /* Hidden by default */
            margin-top: -10px; /* Adjust spacing */
            margin-bottom: 20px;
        }
        .other-input input {
            width: calc(100% - 22px);
            padding: 10px;
            border: 1px dashed #aaa;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .radio-group {
            margin-bottom: 20px;
        }
        .radio-group label {
            display: inline-block;
            margin-right: 15px;
            font-weight: normal;
        }
        .radio-group input[type="radio"] {
            margin-right: 5px;
        }
    </style>
</head>
<body>
    <h1>Editar Componentes de PC</h1>

    <?php if ($successMessage): ?>
        <div class="message success"><?php echo htmlspecialchars($successMessage); ?></div>
    <?php endif; ?>

    <?php if ($errorMessage): ?>
        <div class="message error"><?php echo htmlspecialchars($errorMessage); ?></div>
    <?php endif; ?>

    <?php if ($pcData): // Only display the form if PC data was successfully loaded ?>
        <form action="" method="POST">
            <input type="hidden" name="pc_id" value="<?php echo htmlspecialchars($pcData['id']); ?>">

            <label for="board_type">Tipo de Placa:</label>
            <select id="board_type" name="board_type" required>
                <?php foreach ($boardTypes as $item): ?>
                    <option value="<?php echo htmlspecialchars($item['value']); ?>" <?php echo ($pcData['board_type'] === $item['value']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($item['display'] ?? $item['value']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="cpu_name">Nombre del Procesador:</label>
            <select id="cpu_name" name="cpu_name" required onchange="toggleOtherInput('cpu_name', 'cpu_name_other_container')">
                <?php foreach ($cpuNames as $item): ?>
                    <option value="<?php echo htmlspecialchars($item['id']); ?>" <?php echo ($pcData['cpu_name'] === $item['value']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($item['value']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <div id="cpu_name_other_container" class="other-input">
                <input type="text" name="cpu_name_other" placeholder="Especifique otro nombre de procesador">
            </div>

            <label for="ram_capacity">Capacidad de RAM (GB):</label>
            <select id="ram_capacity" name="ram_capacity" required onchange="toggleOtherInput('ram_capacity', 'ram_capacity_other_container')">
                <?php foreach ($ramCapacities as $item): ?>
                    <option value="<?php echo htmlspecialchars($item['id']); ?>" <?php echo ($pcData['ram_capacity'] === $item['value']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($item['value']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <div id="ram_capacity_other_container" class="other-input">
                <input type="text" name="ram_capacity_other" placeholder="Especifique otra capacidad de RAM (ej. 32GB)">
            </div>

            <label for="ram_type">Tipo de RAM:</label>
            <select id="ram_type" name="ram_type" required>
                <?php foreach ($ramTypes as $item): ?>
                    <option value="<?php echo htmlspecialchars($item['value']); ?>" <?php echo ($pcData['ram_type'] === $item['value']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($item['display'] ?? $item['value']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="disc_capacity">Capacidad de Disco (GB):</label>
            <select id="disc_capacity" name="disc_capacity" required onchange="toggleOtherInput('disc_capacity', 'disc_capacity_other_container')">
                <?php foreach ($discCapacities as $item): ?>
                    <option value="<?php echo htmlspecialchars($item['id']); ?>" <?php echo ($pcData['disc_capacity'] === $item['value']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($item['value']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <div id="disc_capacity_other_container" class="other-input">
                <input type="text" name="disc_capacity_other" placeholder="Especifique otra capacidad de disco (ej. 2TB)">
            </div>

            <label for="disc_type">Tipo de Disco:</label>
            <select id="disc_type" name="disc_type" required>
                <?php foreach ($discTypes as $item): ?>
                    <option value="<?php echo htmlspecialchars($item['value']); ?>" <?php echo ($pcData['disc_type'] === $item['value']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($item['display'] ?? $item['value']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="gpu_name">Nombre de Tarjeta Gráfica:</label>
            <select id="gpu_name" name="gpu_name" onchange="toggleOtherInput('gpu_name', 'gpu_name_other_container')">
                <option value="">Seleccione una tarjeta gráfica (Opcional)</option>
                <?php foreach ($gpuNames as $item): ?>
                    <option value="<?php echo htmlspecialchars($item['id']); ?>" <?php echo ($pcData['gpu_name'] === $item['value']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($item['value']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <div id="gpu_name_other_container" class="other-input">
                <input type="text" name="gpu_name_other" placeholder="Especifique otro nombre de tarjeta gráfica">
            </div>

            <label>Tipo de Tarjeta Gráfica:</label>
            <div class="radio-group">
                <input type="radio" id="gpu_type_integrada" name="gpu_type_radio" value="integrada" <?php echo ($pcData['gpu_type'] === 'integrada') ? 'checked' : ''; ?>>
                <label for="gpu_type_integrada">Integrada</label>

                <input type="radio" id="gpu_type_externa" name="gpu_type_radio" value="externa" <?php echo ($pcData['gpu_type'] === 'externa') ? 'checked' : ''; ?>>
                <label for="gpu_type_externa">Externa</label>
            </div>

            <div class="checkbox-group">
                <input type="checkbox" id="wifi" name="wifi" value="true" <?php echo ($pcData['wifi'] === 'true') ? 'checked' : ''; ?>>
                <label for="wifi">Wi-Fi</label>
            </div>
            <div class="checkbox-group">
                <input type="checkbox" id="bluetooth" name="bluetooth" value="true" <?php echo ($pcData['bluetooth'] === 'true') ? 'checked' : ''; ?>>
                <label for="bluetooth">Bluetooth</label>
            </div>

            <label for="obser">Observaciones:</label>
            <textarea id="obser" name="obser" rows="4"><?php echo htmlspecialchars($pcData['obser']); ?></textarea>

            <input type="submit" name="submit_edit_pc" value="Guardar Cambios">
        </form>
    <?php elseif (!isset($_GET['id'])): ?>
        <p>Por favor, especifique un ID de PC para editar. Ejemplo: `edit_pc.php?id=1`</p>
    <?php endif; ?>

    <script>
        // JavaScript to show/hide 'Other' input fields
        function toggleOtherInput(selectId, otherContainerId) {
            const selectElement = document.getElementById(selectId);
            const otherContainer = document.getElementById(otherContainerId);
            const otherInput = otherContainer ? otherContainer.querySelector('input[type="text"]') : null;

            if (selectElement && otherContainer && otherInput) {
                if (selectElement.value === '_OTHER_') {
                    otherContainer.style.display = 'block';
                    otherInput.setAttribute('required', 'required'); // Make required when visible
                } else {
                    otherContainer.style.display = 'none';
                    otherInput.removeAttribute('required'); // Remove required when hidden
                    otherInput.value = ''; // Clear value when hidden
                }
            }
        }

        // Initialize 'Other' input visibility on page load
        document.addEventListener('DOMContentLoaded', function() {
            // List of select IDs that might have an 'Other' input
            const selectsWithOther = [
                { selectId: 'cpu_name', containerId: 'cpu_name_other_container' },
                { selectId: 'ram_capacity', containerId: 'ram_capacity_other_container' },
                { selectId: 'disc_capacity', containerId: 'disc_capacity_other_container' },
                { selectId: 'gpu_name', containerId: 'gpu_name_other_container' }
            ];

            selectsWithOther.forEach(item => {
                const selectElement = document.getElementById(item.selectId);
                const otherContainer = document.getElementById(item.containerId);

                if (selectElement && otherContainer) {
                    // Check if the current pcData value for this select is not in the predefined list
                    // and if 'Other' is an option in the select.
                    let isOtherSelected = false;
                    const pcDataValue = selectId === 'cpu_name' ? 'cpu_name' :
                                        selectId === 'ram_capacity' ? 'ram_capacity' :
                                        selectId === 'disc_capacity' ? 'disc_capacity' :
                                        selectId === 'gpu_name' ? 'gpu_name' : '';

                    if (pcData && pcData[pcDataValue]) {
                        const existingValues = Array.from(selectElement.options).map(opt => opt.textContent.trim());
                        if (!existingValues.includes(pcData[pcDataValue]) && selectElement.querySelector('option[value="_OTHER_"]')) {
                            selectElement.value = '_OTHER_';
                            otherContainer.style.display = 'block';
                            const otherInput = otherContainer.querySelector('input[type="text"]');
                            if (otherInput) {
                                otherInput.value = pcData[pcDataValue]; // Populate with the actual value
                                otherInput.setAttribute('required', 'required');
                            }
                            isOtherSelected = true;
                        }
                    }

                    if (!isOtherSelected) {
                        // If it's not an 'Other' value or the '_OTHER_' option isn't selected by default
                        // (e.g., if the value matches an existing one), ensure 'Other' input is hidden.
                        if (selectElement.value !== '_OTHER_') {
                             otherContainer.style.display = 'none';
                             const otherInput = otherContainer.querySelector('input[type="text"]');
                             if (otherInput) {
                                 otherInput.removeAttribute('required');
                                 otherInput.value = '';
                             }
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>