<?php
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ticket_name = $_POST["ticket_name"] ?? '';
    
    if (empty($ticket_name)) {
        echo json_encode(['success' => false, 'message' => 'Nombre no válido']);
        exit;
    }
    
    $source = 'pdf/generado.pdf';
    $targetDir = 'pdf/saved/';
    
    if (!file_exists($source)) {
        echo json_encode(['success' => false, 'message' => 'No hay PDF para guardar']);
        exit;
    }
    
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }
    
    $date = date('Y-m-d');
    $target = $targetDir . $date . ' - ' . $ticket_name . '.pdf';

    if (file_exists($target)) {
        echo json_encode(['success' => false, 'message' => 'Ya existe otro archivo con ese nombre']);
        exit;
    }
    if (copy($source, $target)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al copiar el archivo']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
?>