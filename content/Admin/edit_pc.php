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


    // Handle component IDs (CPU, RAM, Disc, GPU) - Now supporting Select2's tags
    try {
        // CPU Name
        $cpuNameId = null;
        $submittedCpuValue = $_POST['cpu_name'] ?? ''; // This can be an ID or a new text from Select2
        if (!empty($submittedCpuValue)) {
            if (is_numeric($submittedCpuValue)) {
                $cpuNameId = (int)$submittedCpuValue; // It's an existing ID
            } else {
                // It's a non-numeric string, meaning a new tag was entered
                $newCpuName = trim(filter_input(INPUT_POST, 'cpu_name', FILTER_UNSAFE_RAW)); // Use submittedCpuValue directly
                if (!empty($newCpuName)) {
                    $cpuNameId = getOrCreateComponentId($pdo, 'cpu', 'name', $newCpuName);
                } else {
                    $errorMessage = "El nombre del procesador no puede estar vacío si se ingresa un valor nuevo.";
                }
            }
        }

        // RAM Capacity
        $ramCapacityId = null;
        $submittedRamCapacityValue = $_POST['ram_capacity'] ?? '';
        if (!empty($submittedRamCapacityValue)) {
            if (is_numeric($submittedRamCapacityValue)) {
                $ramCapacityId = (int)$submittedRamCapacityValue;
            } else {
                $newRamCapacity = trim(filter_input(INPUT_POST, 'ram_capacity', FILTER_UNSAFE_RAW));
                if (!empty($newRamCapacity)) {
                    $ramCapacityId = getOrCreateComponentId($pdo, 'ram', 'capacity', $newRamCapacity);
                } else {
                    $errorMessage = "La capacidad de RAM no puede estar vacía si se ingresa un valor nuevo.";
                }
            }
        }

        // Disc Capacity
        $discCapacityId = null;
        $submittedDiscCapacityValue = $_POST['disc_capacity'] ?? '';
        if (!empty($submittedDiscCapacityValue)) {
            if (is_numeric($submittedDiscCapacityValue)) {
                $discCapacityId = (int)$submittedDiscCapacityValue;
            } else {
                $newDiscCapacity = trim(filter_input(INPUT_POST, 'disc_capacity', FILTER_UNSAFE_RAW));
                if (!empty($newDiscCapacity)) {
                    $discCapacityId = getOrCreateComponentId($pdo, 'disc', 'capacity', $newDiscCapacity);
                } else {
                    $errorMessage = "La capacidad de disco no puede estar vacía si se ingresa un valor nuevo.";
                }
            }
        }

        // GPU Name (Optional field)
        $gpuNameId = null;
        $submittedGpuValue = $_POST['gpu_name'] ?? '';
        if (!empty($submittedGpuValue)) {
            if (is_numeric($submittedGpuValue)) {
                $gpuNameId = (int)$submittedGpuValue;
            } else {
                $newGpuName = trim(filter_input(INPUT_POST, 'gpu_name', FILTER_UNSAFE_RAW));
                if (!empty($newGpuName)) {
                    $gpuNameId = getOrCreateComponentId($pdo, 'gpu', 'name', $newGpuName);
                }
            }
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
            // Select query with JOINs to get actual names/capacities AND their IDs
            $pcQuery = $pdo->prepare("
                SELECT
                    p.id, p.board_type, p.ram_type, p.disc_type, p.gpu_type, p.wifi, p.bluetooth, p.obser,
                    c.id AS cpu_id, c.name AS cpu_name,
                    r.id AS ram_id, r.capacity AS ram_capacity,
                    d.id AS disc_id, d.capacity AS disc_capacity,
                    g.id AS gpu_id, g.name AS gpu_name
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
                    c.id AS cpu_id, c.name AS cpu_name,
                    r.id AS ram_id, r.capacity AS ram_capacity,
                    d.id AS disc_id, d.capacity AS disc_capacity,
                    g.id AS gpu_id, g.name AS gpu_name
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
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        /* Your CSS here */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
            color: #333;
        }

        body.dark {
            background-color: #333;
            color: #f4f4f4;
        }

        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }

        body.dark h1 {
            color: #f4f4f4;
        }

        form {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            margin: 0 auto;
        }

        body.dark form {
            background-color: #444;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #555;
        }

        body.dark label {
            color: #ddd;
        }

        /* Adjustments for Select2 */
        .select2-container--default .select2-selection--single,
        .select2-container--default .select2-selection--multiple {
            border: 1px solid #ddd !important; /* Override default Select2 border */
            border-radius: 4px !important;
            height: auto !important; /* Allow height to adjust */
            min-height: 38px; /* Standard height */
            padding: 5px 10px !important; /* Adjust padding */
            background-color: #f9f9f9 !important;
            color: #333 !important;
            margin-bottom: 15px; /* Add margin bottom like other inputs */
            box-sizing: border-box;
        }

        body.dark .select2-container--default .select2-selection--single,
        body.dark .select2-container--default .select2-selection--multiple {
            background-color: #555 !important;
            border-color: #666 !important;
            color: #f4f4f4 !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 28px !important; /* Adjust text vertical alignment */
            padding-left: 0 !important; /* Remove default left padding */
        }

        .select2-container .select2-selection__clear {
            padding-right: 0 !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px !important; /* Adjust arrow icon height */
            right: 1px !important;
        }

        /* Styling for the dropdown results */
        .select2-container--default .select2-results__option--highlighted.select2-results__option--selectable {
            background-color: #007bff !important;
            color: white !important;
        }

        .select2-container--default .select2-results__option--selected {
            background-color: #e4e6eb !important; /* Light grey for selected */
            color: #333 !important;
        }
        body.dark .select2-container--default .select2-results__option--selected {
            background-color: #666 !important;
            color: #f4f4f4 !important;
        }

        /* Input for typing in new tags */
        .select2-search__field {
            width: 100% !important; /* Make search field take full width */
            padding: 10px !important;
            border: 1px solid #ddd !important;
            border-radius: 4px !important;
            box-sizing: border-box !important;
            background-color: #f9f9f9 !important;
            color: #333 !important;
            margin-bottom: 5px;
        }
        body.dark .select2-search__field {
            background-color: #555 !important;
            border-color: #666 !important;
            color: #f4f4f4 !important;
        }
        .select2-search__field:focus {
            border-color: #007bff !important;
            outline: none !important;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.2) !important;
        }


        select,
        input[type="text"],
        textarea {
            width: calc(100% - 22px); /* Account for padding and border */
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box; /* Include padding and border in the element's total width and height */
            background-color: #f9f9f9;
            color: #333;
        }

        body.dark select,
        body.dark input[type="text"],
        body.dark textarea {
            background-color: #555;
            border-color: #666;
            color: #f4f4f4;
        }

        select:focus,
        input[type="text"]:focus,
        textarea:focus {
            border-color: #007bff;
            outline: none;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.2);
        }

        .other-input {
            display: none; /* Hidden by default */
            margin-top: -5px; /* Adjust to reduce space */
            margin-bottom: 15px;
        }

        .other-input input[type="text"] {
            margin-top: 0;
            margin-bottom: 0;
        }

        .radio-group, .checkbox-group {
            margin-bottom: 15px;
        }

        .radio-group label, .checkbox-group label {
            display: inline-block;
            margin-right: 15px;
            font-weight: normal;
        }

        .radio-group input[type="radio"],
        .checkbox-group input[type="checkbox"] {
            margin-right: 5px;
        }

        input[type="submit"] {
            background-color: #28a745;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            width: auto;
            display: block;
            margin: 20px auto 0;
            transition: background-color 0.3s ease;
        }

        input[type="submit"]:hover {
            background-color: #218838;
        }

        .message {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
            text-align: center;
        }

        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            form {
                margin: 0 10px;
                padding: 15px;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 10px;
            }
            select,
            input[type="text"],
            textarea {
                width: 100%;
            }
        }
    </style>
    <?php if ($pcData): // Only embed if pcData exists ?>
    <script>
        // Pass PHP pcData to JavaScript
        const pcDataFromPHP = <?php echo json_encode($pcData); ?>;
    </script>
    <?php endif; ?>
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
            <select id="cpu_name" name="cpu_name" required>
                <?php
                $selectedCpuId = $pcData['cpu_id']; // ID of the current PC's CPU
                $selectedCpuName = $pcData['cpu_name']; // Name of the current PC's CPU

                // Create a temporary array to hold all options for rendering
                $cpuOptionsForHtml = $dbCpuNames;

                // Check if the current PC's CPU ID is found in the predefined options
                $foundSelectedInPredefined = false;
                foreach ($cpuOptionsForHtml as $option) {
                    if ($selectedCpuId == $option['id']) {
                        $foundSelectedInPredefined = true;
                        break;
                    }
                }

                // If the selected CPU ID is NOT among the predefined options,
                // add it as a new option to ensure it's rendered and selected by default.
                // This handles previously saved "custom" values.
                if (!$foundSelectedInPredefined && !empty($selectedCpuId) && !empty($selectedCpuName)) {
                    $cpuOptionsForHtml[] = ['id' => $selectedCpuId, 'value' => $selectedCpuName];
                }

                // Sort options by value for better display in dropdown
                usort($cpuOptionsForHtml, function($a, $b) {
                    return strcmp($a['value'], $b['value']);
                });

                foreach ($cpuOptionsForHtml as $item):
                    $selected = ($selectedCpuId == $item['id']) ? 'selected' : '';
                ?>
                    <option value="<?php echo htmlspecialchars($item['id']); ?>" <?php echo $selected; ?>>
                        <?php echo htmlspecialchars($item['value']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <label for="ram_capacity">Capacidad de RAM (GB):</label>
            <select id="ram_capacity" name="ram_capacity" required>
                <?php
                $selectedRamId = $pcData['ram_id'];
                $selectedRamCapacity = $pcData['ram_capacity'];

                $ramOptionsForHtml = $dbRamCapacities;
                $foundSelectedInPredefined = false;
                foreach ($ramOptionsForHtml as $option) {
                    if ($selectedRamId == $option['id']) {
                        $foundSelectedInPredefined = true;
                        break;
                    }
                }

                if (!$foundSelectedInPredefined && !empty($selectedRamId) && !empty($selectedRamCapacity)) {
                    $ramOptionsForHtml[] = ['id' => $selectedRamId, 'value' => $selectedRamCapacity];
                }

                usort($ramOptionsForHtml, function($a, $b) {
                    // Sort numerically for capacity
                    return ($a['value'] + 0) - ($b['value'] + 0);
                });

                foreach ($ramOptionsForHtml as $item):
                    $selected = ($selectedRamId == $item['id']) ? 'selected' : '';
                ?>
                    <option value="<?php echo htmlspecialchars($item['id']); ?>" <?php echo $selected; ?>>
                        <?php echo htmlspecialchars($item['value']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <label for="ram_type">Tipo de RAM:</label>
            <select id="ram_type" name="ram_type" required>
                <?php foreach ($ramTypes as $item): ?>
                    <option value="<?php echo htmlspecialchars($item['value']); ?>" <?php echo ($pcData['ram_type'] === $item['value']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($item['display'] ?? $item['value']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="disc_capacity">Capacidad de Disco (GB):</label>
            <select id="disc_capacity" name="disc_capacity" required>
                <?php
                $selectedDiscId = $pcData['disc_id'];
                $selectedDiscCapacity = $pcData['disc_capacity'];

                $discOptionsForHtml = $dbDiscCapacities;
                $foundSelectedInPredefined = false;
                foreach ($discOptionsForHtml as $option) {
                    if ($selectedDiscId == $option['id']) {
                        $foundSelectedInPredefined = true;
                        break;
                    }
                }

                if (!$foundSelectedInPredefined && !empty($selectedDiscId) && !empty($selectedDiscCapacity)) {
                    $discOptionsForHtml[] = ['id' => $selectedDiscId, 'value' => $selectedDiscCapacity];
                }

                usort($discOptionsForHtml, function($a, $b) {
                    // Sort numerically for capacity
                    return ($a['value'] + 0) - ($b['value'] + 0);
                });

                foreach ($discOptionsForHtml as $item):
                    $selected = ($selectedDiscId == $item['id']) ? 'selected' : '';
                ?>
                    <option value="<?php echo htmlspecialchars($item['id']); ?>" <?php echo $selected; ?>>
                        <?php echo htmlspecialchars($item['value']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <label for="disc_type">Tipo de Disco:</label>
            <select id="disc_type" name="disc_type" required>
                <?php foreach ($discTypes as $item): ?>
                    <option value="<?php echo htmlspecialchars($item['value']); ?>" <?php echo ($pcData['disc_type'] === $item['value']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($item['display'] ?? $item['value']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="gpu_name">Nombre de Tarjeta Gráfica:</label>
            <select id="gpu_name" name="gpu_name">
                <option value="">Seleccione una tarjeta gráfica (Opcional)</option>
                <?php
                $selectedGpuId = $pcData['gpu_id'];
                $selectedGpuName = $pcData['gpu_name'];

                $gpuOptionsForHtml = $dbGpuNames; // Start with predefined GPUs

                // Check if the currently selected GPU ID is in the predefined list
                $foundSelectedInPredefined = false;
                foreach ($gpuOptionsForHtml as $option) {
                    if ($selectedGpuId == $option['id']) {
                        $foundSelectedInPredefined = true;
                        break;
                    }
                }
                // If the selected GPU ID is NOT among the predefined options, add it
                // This handles previously saved "custom" GPU names.
                if (!$foundSelectedInPredefined && !empty($selectedGpuId) && !empty($selectedGpuName)) {
                    $gpuOptionsForHtml[] = ['id' => $selectedGpuId, 'value' => $selectedGpuName];
                }

                usort($gpuOptionsForHtml, function($a, $b) {
                    return strcmp($a['value'], $b['value']);
                });

                foreach ($gpuOptionsForHtml as $item):
                    // Ensure the correct option is selected, handling the empty case
                    $selected = '';
                    if (!empty($selectedGpuId) && ($selectedGpuId == $item['id'])) {
                        $selected = 'selected';
                    } elseif (empty($selectedGpuId) && empty($item['id'])) { // For the initial "Select an option"
                        $selected = 'selected';
                    }
                ?>
                    <option value="<?php echo htmlspecialchars($item['id']); ?>" <?php echo $selected; ?>>
                        <?php echo htmlspecialchars($item['value']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
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

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src ="assets/js/script.js"></script>
</body>
</html>