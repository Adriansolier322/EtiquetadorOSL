<?php
include ("../configuration.php");
header('Content-Type: application/json');

$stmt = $conn->prepare("SELECT * FROM sn");
if($stmt-> execute()){
    $result = $stmt->fetchAll();
    echo json_encode($result);
    header("HTTP/1.1 201 OK");
} else {
    header("HTTP/1.1 404 NOT FOUND");
}
?>
