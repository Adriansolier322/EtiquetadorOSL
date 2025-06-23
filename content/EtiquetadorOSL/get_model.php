<?php
include ("configuration.php");
header('Content-Type: application/json');
$modelId = $_GET['modelId'];
$stmt = $conn->prepare("SELECT pc.id, pc.board_type, cpu.name AS cpu_name, pc.ram_type, ram.capacity AS ram_capacity, pc.disc_type, disc.capacity AS disc_capacity, gpu.name AS gpu_name, pc.gpu_type, pc.wifi, pc.bluetooth, pc.obser FROM pc LEFT JOIN cpu ON pc.cpu_name = cpu.id LEFT JOIN ram ON pc.ram_capacity = ram.id LEFT JOIN disc ON pc.disc_capacity = disc.id LEFT JOIN gpu ON pc.gpu_name = gpu.id WHERE pc.id = ?");
if($stmt-> execute([$modelId])){
    $result = $stmt->fetchAll();
    echo json_encode($result);
}
?>