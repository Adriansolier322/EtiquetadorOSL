<?php
include ("configuration.php");
header('Content-Type: application/json');

$stmt = $conn->prepare("SELECT * FROM models");
if($stmt-> execute()){
    $result = $stmt->fetchAll();
    echo json_encode($result);
}
?>