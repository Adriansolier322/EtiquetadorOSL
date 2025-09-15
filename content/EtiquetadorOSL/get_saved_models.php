<?php
include ("configuration.php");
header('Content-Type: application/json');

// Consulta para obtener todos los modelos guardados
$stmt = $conn->prepare("SELECT * FROM models");
if($stmt-> execute()){
    $result = $stmt->fetchAll();
    echo json_encode($result);
}
?>