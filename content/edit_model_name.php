<?php
include ("configuration.php");
header('Content-Type: application/json');

// Verificar que los parámetros existen
if (!isset($_GET['modelId']) || !isset($_GET['modelName'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Parámetros faltantes']);
    exit;
}

$modelId = $_GET['modelId'];
$modelName = $_GET['modelName'];

// Validar que modelId es numérico
if (!is_numeric($modelId)) {
    http_response_code(400);
    echo json_encode(['error' => 'ID de modelo inválido']);
    exit;
}

try {
    $stmt = $conn->prepare("UPDATE models SET name = ? WHERE id = ?");
    $stmt->execute([$modelName, $modelId]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'newName' => $modelName]);
    } else {
        echo json_encode(['error' => 'No se actualizó ningún registro']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]);
}
exit;
?>