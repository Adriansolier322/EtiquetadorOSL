<?php
include ("../configuration.php");
$stmt = $conn->prepare("SELECT pc.id, cpu.name AS cpu_name, ram.capacity AS ram_capacity, pc.ram_type, disc.capacity AS disc_capacity, pc.disc_type, gpu.name AS gpu_name, CONCAT(sn.prefix, LPAD(sn.num, 4, '0')) AS serial_number, pc.obser FROM pc JOIN cpu ON pc.cpu = cpu.id JOIN ram ON pc.ram = ram.id JOIN disc ON pc.disc = disc.id JOIN gpu ON pc.gpu = gpu.id JOIN sn ON pc.sn = sn.id");
if($stmt-> execute()){
    $result = $stmt->fetchAll();
    echo json_encode($result);
    header("HTTP/1.1 201 OK");
} else {
    header("HTTP/1.1 404 NOT FOUND");
}
?>
