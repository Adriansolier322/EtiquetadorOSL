<?php
include ("configuration.php");
header('Content-Type: application/json');
$modelId = $_GET['modelId'];
$stmt = $conn->prepare("DELETE FROM models where id=$modelId");
if($stmt-> execute()){
    $result = $stmt->fetchAll();
    echo json_encode($result);
}
?>