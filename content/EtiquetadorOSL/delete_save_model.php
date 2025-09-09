<?php
include ("configuration.php");
header('Content-Type: application/json');

$modelId = $_GET['modelId'];
// Crear conexión
$stmt = $conn->prepare("DELETE FROM models where id=$modelId");
// Ejecutar la consulta
if($stmt-> execute()){
    $result = $stmt->fetchAll();
    echo json_encode($result);
}
?>