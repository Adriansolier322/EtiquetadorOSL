<?php
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = json_decode(file_get_contents('php://input'), true);
    $pdfPath = $data['path'] ?? '';

    // Validar que el path es seguro (evitar path traversal)
    $baseDir = realpath('pdf/saved/');
    $fullPath = realpath($pdfPath);
    
    if ($fullPath && strpos($fullPath, $baseDir) === 0 && is_file($fullPath)) {
        if (unlink($fullPath)) {
            echo json_encode(['success' => true]);
            exit;
        }
    }

    echo json_encode(['success' => false, 'message' => 'No se pudo eliminar el archivo']);
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
?>