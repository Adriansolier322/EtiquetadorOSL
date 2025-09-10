<?php
require_once 'config.php';
require_once 'auth.php';

header('Content-Type: application/json');

/**
 * Este archivo devuelve estadísticas sobre los PCs en formato JSON.
 * Parámetro GET 'stat' puede ser 'cpu', 'ram', 'disc' o 'gpu' para obtener diferentes estadísticas.
 */ 
try {
    $stat = $_GET['stat'] ?? 'cpu';
    
    switch ($stat) {
        case 'cpu':
            $query = "
                SELECT cpu.name AS label, COUNT(pc.id) AS value
                FROM pc
                JOIN cpu ON pc.cpu_name = cpu.id
                GROUP BY cpu.name
                ORDER BY value DESC
            ";
            break;
            
        case 'ram':
            $query = "
                SELECT CONCAT(ram.capacity, 'GB ', pc.ram_type) AS label, COUNT(pc.id) AS value
                FROM pc
                JOIN ram ON pc.ram_capacity = ram.id
                GROUP BY ram.capacity, pc.ram_type
                ORDER BY ram.capacity
            ";
            break;
            
        case 'disc':
            $query = "
                SELECT CONCAT(disc.capacity, 'GB ', pc.disc_type) AS label, COUNT(pc.id) AS value
                FROM pc
                JOIN disc ON pc.disc_capacity = disc.id
                GROUP BY disc.capacity, pc.disc_type
                ORDER BY disc.capacity
            ";
            break;
            
        case 'gpu':
            $query = "
                SELECT 
                    CASE 
                        WHEN pc.gpu_name IS NULL THEN 'Sin GPU' 
                        ELSE CONCAT(gpu.name, ' (', pc.gpu_type, ')') 
                    END AS label, 
                    COUNT(pc.id) AS value
                FROM pc
                LEFT JOIN gpu ON pc.gpu_name = gpu.id
                GROUP BY pc.gpu_name, pc.gpu_type, gpu.name
                ORDER BY value DESC
            ";
            break;
            
        default:
            throw new Exception("Tipo de estadística no válido");
    }
    // Ejecutar la consulta
    $stmt = $pdo->query($query);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // Formatear los datos para la respuesta JSON
    $data = [
        'labels' => array_column($results, 'label'),
        'values' => array_column($results, 'value')
    ];
    
    echo json_encode($data);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>