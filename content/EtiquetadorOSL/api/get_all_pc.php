<?php
include ("../configuration.php");
header('Content-Type: application/json');

$stmt = $conn->prepare("SELECT 
    pc.id, 
    cpu.name AS cpu_name, 
    ram.capacity AS ram_capacity, 
    pc.ram_type, 
    disc.capacity AS disc_capacity, 
    pc.disc_type, 
    gpu.name AS gpu_name, 
    pc.gpu_type,
    pc.wifi,
    pc.bluetooth,
    pc.obser

FROM 
    pc
JOIN 
    cpu ON pc.cpu_name = cpu.id
JOIN 
    ram ON pc.ram_capacity = ram.id
JOIN 
    disc ON pc.disc_capacity = disc.id
JOIN 
    gpu ON pc.gpu_name = gpu.id
");
if($stmt-> execute()){
    $result = $stmt->fetchAll();
    echo json_encode($result);
    header("HTTP/1.1 201 OK");
} else {
    header("HTTP/1.1 404 NOT FOUND");
}
?>
